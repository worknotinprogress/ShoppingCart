<?php

namespace App\Command;

use App\Service\FileProcessor;
use App\Service\ProductDataConverter;
use App\Service\ShoppingCartService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CartProcessProductsCommand
 * @package App\Command
 */
class CartProcessProductsCommand extends Command
{
    protected static $defaultName = 'cart:process-products';

    /**
     * @var FileProcessor
     */
    protected $_fileProcessor;

    /**
     * @var ProductDataConverter
     */
    private $_productDataConverter;

    /**
     * @var ShoppingCartService
     */
    private $_shoppingCartService;

    /**
     * CartProcessProductsCommand constructor.
     * @param null $name
     * @param FileProcessor $fileProcessor
     * @param ProductDataConverter $productDataConverter
     * @param ShoppingCartService $shoppingCartService
     */
    public function __construct(
        $name = null,
        FileProcessor $fileProcessor,
        ProductDataConverter $productDataConverter,
        ShoppingCartService $shoppingCartService)
    {
        $this->_fileProcessor = $fileProcessor;
        $this->_productDataConverter = $productDataConverter;
        $this->_shoppingCartService = $shoppingCartService;
        parent::__construct($name);
    }

    /**
     * Command configuration
     */
    protected function configure()
    {
        $this
            ->setDescription('Adds products to shopping cart from uploaded file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'File path')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');
        $io->note(sprintf('You passed file path: %s', $filePath));

        $this->_fileProcessor->processFile($filePath);

        if ($this->_fileProcessor->hasErrors()){
            foreach ($this->_fileProcessor->getErrors() as $error){
                $io->error($error);
            }
            return null;
        }

        $this->_productDataConverter->convertProducts($this->_fileProcessor->getFileInformation());

        if ($this->_productDataConverter->hasErrors()) {
            foreach ($this->_productDataConverter->getErrors() as $error) {
                $io->error($error);
            }
        }

        $products = $this->_productDataConverter->getProducts();
        $customer = $this->_shoppingCartService->saveCustomerByFile($filePath);
        $this->_shoppingCartService->productToShoppingCart($products, $customer);

        $io->success('Shopping cart successfully updated');
    }
}
