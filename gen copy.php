<?php

if ($argc < 2) die("💥 Пример использование: php {$argv[0]} gen.txt\n");

$structureFile = $argv[1];

// Проверяем, существует ли файл с заданием
if (!file_exists($structureFile)) {
    die("💥 Ошибка: Файл структуры '$structureFile' не найден.\n");
}

// Читаем структуру
$lines = file($structureFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$models = parseStructure($lines);
print_r($models);
exit;
// Обрабатываем каждую модель
foreach ($models as $model => $fields) {
    $model = ucfirst(strtolower($model));
    if (str_contains($model, '_')) {
        $table = trim(strtolower($model)); // Пивотная таблица остается в нижнем регистре
    } else {
        $table = trim(shell_exec("php artisan tinker --execute=\"echo Illuminate\Support\Str::plural('$model')\""));
    }

    echo "\n🚀  {$model} -> {$table}  🚀\n";
    echo "====================================\n";
    generateModel($model, $fields);
    generateMigration($model, $fields, $table);
    generateService($model);
    generateResource($model, $fields);
    generateController($model);
    generateRequests($model, $fields);
    generateApiRoute($model);

   exit;
}

// 🔥 Разбираем структуру
function parseStructure($lines)
{
    $models = [];
    $currentModel = null;

    foreach ($lines as $line) {
        // Убираем пробелы
        if (trim($line) === '') continue;

        // Если строка начинается без пробелов - это модель
        if (strpos($line, ' ') === false && strpos($line, "\t") === false) {
            $model = ucfirst(trim($line));

            // Определяем, является ли таблица пивотной (содержит `_`)
            $isPivot = strpos($model, '_') !== false;
            $lowtable = trim(shell_exec("php artisan tinker --execute=\"echo Illuminate\Support\Str::snake(Illuminate\Support\Str::plural('$model'))\""));
            $table = ucfirst($lowtable); // PascalCase для удобства


            // Создаём структуру модели
            $models[$model] = [
                'model'    => $model,
                'lowmodel' => strtolower($model),
                'table'    => $table,
                'lowtable' => $lowtable,
                'pivot'    => $isPivot ? 1 : 0, // Отметка для пивотных таблиц
                'fields'   => [],
            ];

            $currentModel = $model;
        } 
        // Если строка содержит `=`, значит это параметр
        elseif ($currentModel && strpos($line, '=') !== false) {
            list($field, $definition) = array_map('trim', explode('=', $line, 2));
            $parts = array_map('trim', explode('|', $definition));
            $modifiers = [];

            foreach ($parts as $mod) {
                $mod = trim($mod);

                // Проверяем, является ли модификатор FK
                if (strpos($mod, 'FK') === 0) {
                    if ($mod === 'FK' || $mod === 'FK()') {
                        // FK без параметров → foreignId()->constrained()
                        $modifiers[] = "foreignId('$field')";
                        $modifiers[] = "constrained()";
                    } else {
                        // FK(table, column) → foreign()->references()->on()
                        $params = substr($mod, 3, -1); // Убираем "FK(" и ")"
                        $params = explode(',', $params);
                        if (count($params) === 2) {
                            $table = trim($params[0]);
                            $column = trim($params[1]);
                            $modifiers[] = "foreign('$field')";
                            $modifiers[] = "references('$column')";
                            $modifiers[] = "on('$table')";
                        }
                    }
                } else {
                    // Если параметров нет, добавляем `()`
                    if (strpos($mod, '(') === false) {
                        $mod .= '()';
                    }
                    $modifiers[] = $mod;
                }
            }

            // Добавляем в `fields`
            $models[$currentModel]['fields'][$field] = $modifiers;
        }
    }

    return $models;
}

// Функция генерации строк с типами полей
function generateSchemaFields($model, $fields)
{
    $result = "";
    $result .= "\$table->id();\n"; // ID первичный ключ

    foreach ($fields as $name => $definition) {
        $result .= parseFieldDefinition($name, $definition) . ";\n";
    }
    
    if (str_contains($model, '_')) {
        //если это пивотная таблица - то timestamps - не прописываем
        echo "⚠️  Для пивотных таблиц timestamps не требуется!\n";
    } else {
        $result .= "\$table->timestamps();"; // Дата создания и обновления
    }
    return $result;
}

// Функция обработки типа данных и модификаторов (default, unique, nullable и т. д.)
function parseFieldDefinition($name, $definition)
{
    if (strpos($definition, 'FK()') !== false) {
        // Проверяем, есть ли nullable()
        $isNullable = strpos($definition, '->nullable()') !== false;

        // Убираем nullable() из общего списка модификаторов
        $definition = str_replace('->nullable()', '', $definition);

        // Создаём базовое поле с учетом nullable()
        $field = "\$table->foreignId('$name')" . ($isNullable ? "->nullable()" : "") . "->constrained()";

        // Ищем дополнительные модификаторы (кроме nullable, оно уже обработано)
        preg_match_all('/->(\w+)\((.*?)\)/', $definition, $modMatches, PREG_SET_ORDER);
        foreach ($modMatches as $mod) {
            $modifier = $mod[1];
            $modifierValue = trim($mod[2]) !== '' ? $mod[2] : '';

            $field .= "->$modifier(" . ($modifierValue !== '' ? $modifierValue : '') . ")";
        }

        return $field;
    }

    // Разбираем основные атрибуты
    preg_match('/(\w+)(?:\((\d*)\))?(.*)/', $definition, $matches);

    $type = $matches[1] ?? 'string'; // Основной тип данных
    $size = $matches[2] ?? ''; // Размер (если указан)
    $modifiers = trim($matches[3]) ?? ''; // Остальные модификаторы (unique, default и т. д.)

    // Формируем строку для миграции
    $field = "\$table->$type('$name'" . ($size !== '' ? ", $size" : '') . ")";

    // Добавляем модификаторы, если они есть
    if ($modifiers) {
        preg_match_all('/->(\w+)\((.*?)\)/', $modifiers, $modMatches, PREG_SET_ORDER);
        foreach ($modMatches as $mod) {
            $modifier = $mod[1];
            $modifierValue = trim($mod[2]) !== '' ? $mod[2] : '';

            $field .= "->$modifier(" . ($modifierValue !== '' ? $modifierValue : '') . ")";
        }
    }
    return $field;
}



function genValidationStoreRequest($model, $fields, $table)
{
    //$table = trim(shell_exec("php artisan tinker --execute=\"echo(Illuminate\Support\Str::plural('$model'))\""));
    $lowtable=strtolower($table);
    $rules = "        return [\n";

    foreach ($fields as $field => $definition) {
        $rules .= "            '{$field}' => '" . parseStoreValidationDefinition($field, $definition, $lowtable) . "',\n";
    }

    $rules .= "        ];\n";
    return $rules;
}

function parseStoreValidationDefinition($field, $definition, $table)
{
    $rules = [];

    // Если это Foreign Key
    if (strpos($definition, 'FK()') !== false) {
        $rules[] = "exists:" . str_replace('_id', 's,id', $field);
    } else {
        // Определение типа данных
        if (preg_match('/string\((\d+)\)/', $definition, $match)) {
            $rules[] = 'string';
            $rules[] = "max:{$match[1]}";
        } elseif (strpos($definition, 'text') !== false) {
            $rules[] = 'string';
        } elseif (strpos($definition, 'integer') !== false || strpos($definition, 'tinyInteger') !== false) {
            $rules[] = 'integer';
        } elseif (strpos($definition, 'boolean') !== false) {
            $rules[] = 'boolean';
        } elseif (strpos($definition, 'dateTime') !== false) {
            $rules[] = 'date';
        }

        // Проверяем `unique()`
        if (strpos($definition, 'unique') !== false) {
            $rules[] = "unique:{$table},{$field}";
        }

        // Проверяем `min()`
        if (preg_match('/min\((\d+)\)/', $definition, $minMatch)) {
            $rules[] = "min:{$minMatch[1]}";
        }

        // Проверяем `max()`
        if (preg_match('/max\((\d+)\)/', $definition, $maxMatch)) {
            $rules[] = "max:{$maxMatch[1]}";
        }

        // Добавляем `required`, если нет `nullable` или `default`
        if (strpos($definition, 'default') === false && strpos($definition, 'nullable') === false) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
    }

    return implode('|', $rules);
}

         
function genValidationUpdateRequest($model, $fields, $table)
{
    //$table = trim(shell_exec("php artisan tinker --execute=\"echo(Illuminate\Support\Str::plural('$model'))\""));
    $lowtable=strtolower($table);
    $rules = "        return [\n";

    foreach ($fields as $field => $definition) {
        $rules .= "            '{$field}' => '" . parseUpdateValidationDefinition($field, $definition, $lowtable) . "',\n";
    }

    $rules .= "        ];\n";
    return $rules;
}

// 🔹 Функция разбора атрибутов и формирования правил валидации для UpdateRequest
function parseUpdateValidationDefinition($field, $definition, $table)
{
    $rules = [];

    // Если это Foreign Key
    if (strpos($definition, 'FK()') !== false) {
        $rules[] = "exists:" . str_replace('_id', 's,id', $field);
    } else {
        // Определение типа данных
        if (preg_match('/string\((\d+)\)/', $definition, $match)) {
            $rules[] = 'string';
            $rules[] = "max:{$match[1]}";
        } elseif (strpos($definition, 'text') !== false) {
            $rules[] = 'string';
        } elseif (strpos($definition, 'integer') !== false || strpos($definition, 'tinyInteger') !== false) {
            $rules[] = 'integer';
        } elseif (strpos($definition, 'boolean') !== false) {
            $rules[] = 'boolean';
        } elseif (strpos($definition, 'dateTime') !== false) {
            $rules[] = 'date';
        }

        // Проверяем `unique()`
        if (strpos($definition, 'unique') !== false) {
            $rules[] = "unique:{$table},{$field},{\$this->id}";
        }

        // Проверяем `min()`
        if (preg_match('/min\((\d+)\)/', $definition, $minMatch)) {
            $rules[] = "min:{$minMatch[1]}";
        }

        // Проверяем `max()`
        if (preg_match('/max\((\d+)\)/', $definition, $maxMatch)) {
            $rules[] = "max:{$maxMatch[1]}";
        }

        // Добавляем `sometimes`, чтобы поле не было обязательным при обновлении
        array_unshift($rules, 'sometimes');

        // Добавляем `nullable`, если указано
        if (strpos($definition, 'default') !== false || strpos($definition, 'nullable') !== false) {
            $rules[] = 'nullable';
        }
    }

    return implode('|', $rules);
}


// CLI Check Create Control
function CCCC($path, $cmd, $refresh = true)
{
    // Используем glob() для поиска файлов (даже если в имени есть дата)
    $existingFiles = glob($path);

    // Если файлы уже существуют
    if (!empty($existingFiles)) {
        if ($refresh) {
            foreach ($existingFiles as $file) {
                echo "🗑  Удаляем файл: $file\n";
                unlink($file);
            }
        } else {
            echo "✅  Файл уже существует: {$existingFiles[0]}\n";
            return true; 
        }
    }

    // Запускаем команду CLI
    echo "🚀  Выполняем команду: $cmd\n";
    shell_exec($cmd);

    // Повторно проверяем, создан ли файл
    $newFiles = glob($path);

    if (!empty($newFiles)) {
        echo "✅  Файл успешно создан: {$newFiles[0]}\n";
        return true;
    } else {
        echo "❌  Файл не создан: {$path}\n";
        return false;
    }
}

function replaceCodeInFile($filePath, $search, $putCode)
{
    $code = file_get_contents($filePath);

    $startPos = strpos($code, $search);
    if ($startPos === false) {
        echo "❌  Ошибка: строка '{$search}' не найдена в файле.\n";
        return false;
    }

    // Найти первую { после искомой строки
    $braceOpenPos = strpos($code, '{', $startPos);
    if ($braceOpenPos === false) {
        echo "❌  Ошибка: не найдена { после '{$search}'.\n";
        return false;
    }

    // Найти соответствующую }
    $braceCount = 1;
    $pos = $braceOpenPos + 1;

    while ($braceCount > 0 && $pos < strlen($code)) {
        if ($code[$pos] === '{') {
            $braceCount++;
        } elseif ($code[$pos] === '}') {
            $braceCount--;
        }
        $pos++;
    }

    if ($braceCount > 0) {
        echo "❌  Ошибка: не найдена закрывающая } для '{$search}'.\n";
        return false;
    }

    // Вырезаем старое содержимое и вставляем новое
    $newCode = substr($code, 0, $braceOpenPos + 1) . "\n" . $putCode . "\n        " . substr($code, $pos - 1);

    // Записываем обновлённый код обратно в файл
    file_put_contents($filePath, $newCode);

    return true;
}

function generateModel($model, $fields)
{
    if (str_contains($model, '_')) {
        echo "⚠️  Для пивотных таблиц моедь не требуется!\n";
        return 0;
    }

    $filelPath = "app/Models/$model.php";
    $artisanCmd = "php artisan make:model {$model}";
    if (CCCC($filelPath, $artisanCmd, true)) {
        $fileContent = file_get_contents($filelPath);
        $search = "class $model extends Model";
        $fillable = "'" . implode("', '", array_keys($fields)) . "'";
        $putCode = "    protected \$fillable = [$fillable];";
        if (replaceCodeInFile($filelPath, $search, $putCode)) echo "✅  Класс $model переписаны.\n";
    };
}


function generateMigration($model, $fields, $table) {
    //$table = trim(shell_exec("php artisan tinker --execute=\"echo(Illuminate\Support\Str::plural('$model'))\""));
    $lowtable=strtolower($table);
    $filelPath = "database/migrations/*_create_".strtolower($table)."_table.php";
    $artisanCmd = "php artisan make:migration create_".$lowtable."_table --create=$lowtable";
    if (CCCC($filelPath, $artisanCmd, true)) {
        //Редактируем фаил
        $migrationFiles = glob($filelPath);
        if (!empty($migrationFiles)) {
            $migrationFile = $migrationFiles[0]; // Берём первый найденный файл
            $search = "Schema::create('$lowtable', function (Blueprint \$table)";
            $putCode = generateSchemaFields($model, $fields);
            echo "✅  Обновляем миграцию: $migrationFile\n";
            echo "$putCode";
            exit;
            if (replaceCodeInFile($migrationFile, $search, $putCode)) {
                echo "✅  Миграция $model переписаны.\n";
            };
        } else {
            echo "❌  Миграция не найдена\n";
        }
    } 
}

function generateService($model){
    if (str_contains($model, '_')) {
        echo "⚠️  Для пивотных таблиц Service не требуется!\n";
        return 0;
    }
    $filelPath = "app/Services/{$model}Service.php";
    $artisanCmd = "php artisan make:class Services/{$model}Service";
    if (CCCC($filelPath, $artisanCmd, true)) {
        $fileContent = file_get_contents($filelPath);

        if (!preg_match("/use\s+{$model};/", $fileContent)) {
            $fileContent = preg_replace('/(namespace\s+[\w\\\\]+;)/', "$1\n\nuse App\Models\\$model;", $fileContent, 1);
        }

        // Добавляем методы `store()` и `update()`, если их нет
        if (!preg_match('/public\s+static\s+function\s+store/', $fileContent)) {
            $methods = ""; // Инициализация пустой строки
            // store
            $methods .= "    public static function store(array \$data): {$model}\n";
            $methods .= "    {\n";
            $methods .= "        return {$model}::create(\$data);\n";
            $methods .= "    }\n\n";
            // update
            $methods .= "    public static function update({$model} \$entity, array \$data): {$model}\n";
            $methods .= "    {\n";
            $methods .= "        \$entity->update(\$data);\n";
            $methods .= "        return \$entity;\n";
            $methods .= "    }\n\n";
            
            $fileContent = preg_replace('/}\s*$/', $methods . "}\n", $fileContent);
        }

        // Записываем обновлённое содержимое обратно в файл
        file_put_contents($filelPath, $fileContent);
    } else {
        echo "❌  Ошибка: файл не создан - $filelPath\n"; 
    }
}

// Функция обновления ресурсного файла
function generateResource($model, $fields)
{
    if (str_contains($model, '_')) {
        echo "⚠️  Для пивотных таблиц Resource не требуется!\n";
        return 0;
    }
    $filelPath = "app/Http/Resources/{$model}/{$model}Resource.php";
    $artisanCmd = "php artisan make:resource {$model}/{$model}Resource";

    if (CCCC($filelPath, $artisanCmd, true)) {
        $fileContent = file_get_contents($filelPath);

        //  Формируем массив свойств
        $properties = [];
        foreach ($fields as $name => $definition) {
            if (strpos($definition, 'FK()') !== false) {
                $properties[] = "            '{$name}' => \$this->{$name}, // Foreign Key";
            } else {
                $properties[] = "            '{$name}' => \$this->{$name}";
            }
        }
        $properties[] = "            'updated_at' => \$this->updated_at->format('Y-m-d H:i:s')";
        $propertiesString = implode(",\n", $properties);

        //  Генерируем метод toArray()
        $request = "";
        $request .= "public function toArray(Request \$request): array\n";
        $request .= "    {\n";
        $request .= "        return [\n";
        $request .= "$propertiesString\n";
        $request .= "        ];\n";
        $request .= "    }\n";

        //  Удаляем `return parent::toArray($request);`, если он есть
        $fileContent = preg_replace(
            '/public\s+function\s+toArray\(Request\s+\$request\):\s*array\s*{.*?return\s*parent::toArray\(\$request\);.*?}/s',
            $request,
            $fileContent
        );

        //  Обновляем `toArray()`, если он уже есть
        if (preg_match('/public\s+function\s+toArray\(/', $fileContent)) {
            echo "🔄  Обновляем `toArray()` в {$model}Resource.php\n";
            $fileContent = preg_replace('/public\s+function\s+toArray\(Request\s+\$request\):\s*array\s*{.*?return\s*\[.*?];\s*}/s', $request, $fileContent);
        } else {
            //  Если `toArray()` не найден, добавляем его перед `}`
            echo "🆕  Добавляем `toArray()` в {$model}Resource.php\n";
            $fileContent = preg_replace('/}\s*$/', $request . "}\n", $fileContent);
        }

        //  Записываем обновлённое содержимое обратно в файл
        file_put_contents($filelPath, $fileContent);
        echo "✅  Файл {$model}Resource.php обновлён!\n";
    }
}

function generateController($model)
{
    if (str_contains($model, '_')) {
        echo "⚠️  Для пивотных таблиц Controller не требуется!\n";
        return 0;
    }
    $filelPath = "app/Http/Controllers/Api/{$model}Controller.php";
    $artisanCmd = "php artisan make:controller Api/{$model}Controller --api -m {$model}";

    if (CCCC($filelPath, $artisanCmd, true)) {
        $fileContent = file_get_contents($filelPath);

        // ✅ Удаляем дублирующиеся пустые методы (оставляем только docblock)
        $fileContent = preg_replace('/\/\*\*.*?\*\/\s*public\s+function\s+\w+\(.*?\)\s*{.*?}/s', '', $fileContent);

        // ✅ Добавляем use App\Models\{Model};, если его нет
        $modelNamespace = "App\\Models\\{$model}";
        if (!preg_match("/use\s+" . preg_quote($modelNamespace, '/') . ";/", $fileContent)) {
            $fileContent = preg_replace('/(namespace\s+[\w\\\\]+;)/', "$1\n\nuse {$modelNamespace};", $fileContent, 1);
        }

        // ✅ Добавляем use для Request, Service и Resource
        $imports = [
            "use App\Http\Requests\Api\\$model\StoreRequest;",
            "use App\Http\Requests\Api\\$model\UpdateRequest;",
           // App\Http\Requests\Api\Post\
            "use App\Http\Resources\\{$model}\\{$model}Resource;",
            "use App\Services\\{$model}Service;",
        ];

        foreach ($imports as $import) {
            if (!preg_match("/" . preg_quote($import, '/') . "/", $fileContent)) {
                $fileContent = preg_replace('/(namespace\s+[\w\\\\]+;)/', "$1\n" . implode("\n", $imports), $fileContent, 1);
            }
        }

        // ✅ Генерируем методы контроллера
        $methods = "";

        // index()
        $methods .= "    public function index()\n";
        $methods .= "    {\n";
        $methods .= "        return {$model}Resource::collection({$model}::all());\n";
        $methods .= "    }\n\n";

        // store()
        $methods .= "    public function store(StoreRequest \$request)\n";
        $methods .= "    {\n";
        $methods .= "        \$data = \$request->validated();\n";
        $methods .= "        \$entity = {$model}Service::store(\$data);\n";
        $methods .= "        return new {$model}Resource(\$entity);\n";
        $methods .= "    }\n\n";

        // show()
        $methods .= "    public function show({$model} \$entity)\n";
        $methods .= "    {\n";
        $methods .= "        return new {$model}Resource(\$entity);\n";
        $methods .= "    }\n\n";

        // update()
        $methods .= "    public function update(UpdateRequest \$request, {$model} \$entity)\n";
        $methods .= "    {\n";
        $methods .= "        \$data = \$request->validated();\n";
        $methods .= "        \$entity = {$model}Service::update(\$entity, \$data);\n";
        $methods .= "        return new {$model}Resource(\$entity);\n";
        $methods .= "    }\n\n";

        // destroy()
        $methods .= "    public function destroy({$model} \$entity)\n";
        $methods .= "    {\n";
        $methods .= "        \$id = \$entity->id;\n";
        $methods .= "        \$title = \$entity->title ?? '';\n";
        $methods .= "        \$entity->delete();\n\n";
        $methods .= "        return response([\n";
        $methods .= "            'message' => \"{$model}: \$id (\$title) успешно удалён\",\n";
        $methods .= "        ], 200);\n";
        $methods .= "    }\n";

        // ✅ Вставляем методы в конец класса перед `}`
        $fileContent = preg_replace('/}\s*$/', "\n{$methods}\n}\n", $fileContent);

        // ✅ Записываем обновлённое содержимое
        file_put_contents($filelPath, $fileContent);
        echo "✅  Контроллер {$model}Controller создан и обновлён!\n";
    } else {
        echo "❌  Ошибка: контроллер не создан - $filelPath\n";
    }
}

function generateRequests($model, $fields)
{
    if (str_contains($model, '_')) {
        echo "⚠️  Для пивотных таблиц Requests не требуется!\n";
        return 0;
    }
    $filelPath = "app/Http/Requests/Api/{$model}/StoreRequest.php";
    $artisanCmd = "php artisan make:request Api/{$model}/StoreRequest";
    if (CCCC($filelPath, $artisanCmd, true)) {
        $fileContent = file_get_contents($filelPath);
        
        $search = "public function authorize(): bool";
        $putCode = "        return true;";
        if (replaceCodeInFile($filelPath, $search, $putCode)) echo "✅  Отключил авторизацию.\n";
        $search = "public function rules(): array";
        $putCode = genValidationStoreRequest($model, $fields, $table);
        if (replaceCodeInFile($filelPath, $search, $putCode)) echo "✅  StoreRequest для  {$model} переписаны.\n";
    } else {
        echo "❌  Ошибка: файл не создан - $filelPath\n"; 
    }

    $filelPath = "app/Http/Requests/Api/{$model}/UpdateRequest.php";
    $artisanCmd = "php artisan make:request Api/{$model}/UpdateRequest";
    if (CCCC($filelPath, $artisanCmd, true)) {
        $fileContent = file_get_contents($filelPath);
        $search = "public function authorize(): bool";
        $putCode = "return true;";
        if (replaceCodeInFile($filelPath, $search, $putCode)) echo "✅  Отключил авторизацию.\n";
        $search = "public function rules(): array";
        $putCode = genValidationUpdateRequest($model, $fields, $table);
        
        if (replaceCodeInFile($filelPath, $search, $putCode)) echo "✅  UpdateRequest для  {$model} переписаны.\n";
    } else {
        echo "❌  Ошибка: файл не создан - $filelPath\n"; 
    }
}

function generateApiRoute($model)
{
    if (str_contains($model, '_')) {
        echo "⚠️  Для пивотных таблиц ApiRoute не требуется!\n";
        return 0;
    }
    $filelPath = "routes/api.php";
    $controller = "App\\Http\\Controllers\\Api\\{$model}Controller";
    $routeDefinition = "Route::apiResource('" . strtolower($model) . "s', {$model}Controller::class);";

    // Проверяем, существует ли файл маршрутов
    if (!file_exists($filelPath)) {
        echo "❌  Ошибка: файл маршрутов не найден - $filelPath\n";
        return false;
    }

    // Читаем содержимое файла
    $fileContent = file_get_contents($filelPath);

    // Добавляем `use` для контроллера, если его нет
    $useStatement = "use {$controller};";
    if (!preg_match("/" . preg_quote($useStatement, '/') . "/", $fileContent)) {
        $fileContent = preg_replace('/<\?php\s*/', "<?php\n\n{$useStatement}\n", $fileContent, 1);
    }

    // Добавляем маршрут, если его ещё нет
    if (!preg_match("/" . preg_quote($routeDefinition, '/') . "/", $fileContent)) {
        $fileContent .= "{$routeDefinition}\n";
        echo "✅  Добавлен маршрут API для {$model}\n";
    } else {
        echo "⚠️  Маршрут API для {$model} уже существует\n";
    }

    // Записываем обновлённый файл
    file_put_contents($filelPath, $fileContent);
    return true;
}
