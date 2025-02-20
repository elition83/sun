#Установка Laravel и певый запуск
```bash
sudo apt update && sudo apt upgrade -y
nano /etc/php/8.4/apache2/php.ini
sudo apt install php-sqlite3

sudo apt install mysql-server
sudo systemctl status mysql
sudo mysql_secure_installation
sudo systemctl restart mysql
mysql
```
```bash
nano env -> 
```
```php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sun
DB_USERNAME=sun
DB_PASSWORD=

php artisan migrate
php artisan serv
```

#Первые настройки Laravel
Отглючаем глобально защиту массового заполнения
/app/Providers/AppServiceProvider.php
```php
    use Illuminate\Database\Eloquent\Model; 
    public function boot(): void
    {
        Model::unguard();  //Отключаем защиту filable глобально
    }
```

php artisan make:model Post -m

php artisan migrate
php artisan migrate:refresh - перезоздает все предварительно все затерев

php artisan make:command CreatePost
