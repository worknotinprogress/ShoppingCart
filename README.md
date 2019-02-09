# ShoppingCart

Requirements:
- Server version: 10.1.37-MariaDB
- PHP 7.3.2

To setup this application you'll need to:

1. rename .env-dev to .env
2. in evn. file change database logins
3. run command composer install
4. run command "./bin/console doctrine:database:create"
5. run command "./bin/console doctrine:migrations:migrate"

Instructions:
- Run command ".bin/console  cart:process-products full_path_to_file" example "/home/www/src/shoppingcart/temp/customer1.csv"
- Some demo files are in shoppingcart/temp directory
- Run command ".bin/console  cart:show-shopping-carts" -- it will display all processed carts
- Run command ".bin/console  cart:show-shopping-carts 'customer1.csv'" -- it will display one persons cart.

Future updates:
- Add logger, to log shopping cart changes.
- Add cron, that checks all files in src/temp directory and process then automatically.
- Move currencies from configuration file to database.