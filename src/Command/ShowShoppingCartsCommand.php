<?php

namespace App\Command;

use App\Service\ShoppingCartService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ShowShoppingCartsCommand
 * @package App\Command
 */
class ShowShoppingCartsCommand extends Command
{
    protected static $defaultName = 'cart:show-shopping-carts';

    /**
     * @var ShoppingCartService
     */
    private $_shoppingCartService;

    /**
     * CartProcessProductsCommand constructor.
     * @param null $name
     * @param ShoppingCartService $shoppingCartService
     */
    public function __construct(
        $name = null,
        ShoppingCartService $shoppingCartService)
    {
        $this->_shoppingCartService = $shoppingCartService;
        parent::__construct($name);
    }

    /**
     * Command configuration
     */
    protected function configure()
    {
        $this
            ->setDescription('Show all shopping carts or specific when file path is specified')
            ->addArgument('filePath', InputArgument::OPTIONAL, 'File path')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filePath');
        $io = new SymfonyStyle($input, $output);

        if ($filePath){
            $shoppingCarts = $this->_shoppingCartService->getSpecificShoppingCart($filePath);
            if(!$shoppingCarts){
                $io->note('No shopping cart at the moment');
            }
            $this->buildShoppingCartTable($shoppingCarts, $output, $input);
        } else {
            $customers = $this->_shoppingCartService->getAllShoppingCarts();
            if (!$customers){
                $io->note('No shopping carts at the moment');
            }
            foreach ($customers as $customer){
                $this->buildShoppingCartTable($customer->getShoppingCart()->toArray(), $output, $input);
            }
        }

    }

    /**
     * @param array $shoppingCarts
     * @param OutputInterface $output
     * @param InputInterface $input
     */
    protected function buildShoppingCartTable(array $shoppingCarts, OutputInterface $output, InputInterface $input)
    {
        if ($shoppingCarts) {
            $rowCount = $totalPrice = 0;
            $table = new Table($output);
            $output->writeln('FileHash: ' . $shoppingCarts[0]->getCustomer()->getFileHash());
            $table->setHeaders(['Identifier', 'Name', 'Quantity', 'Price']);

            foreach ($shoppingCarts as $key => $shoppingCart) {
                $table->setRow($rowCount++,[
                    $shoppingCart->getIdentifier(),
                    $shoppingCart->getName(),
                    $shoppingCart->getQuantity(),
                    round($shoppingCart->getPrice(), 2).' '.$shoppingCart->getCurrency()
                ]);
                $totalPrice +=  $shoppingCart->getQuantity() * $shoppingCart->getPrice();
            }
            $table->setRow($rowCount++, [new TableSeparator(), new TableSeparator(), new TableSeparator(), new TableSeparator()]);
            $table->setRow($rowCount, ['Total cart amount', round($totalPrice, 2).' '.$shoppingCarts[0]->getCurrency()]);
            $table->render();
        }
    }
}
