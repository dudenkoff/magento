<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * CLI Command demonstrating Model, ResourceModel, Collection, and Repository patterns
 */

namespace Dudenkoff\MVVMLearn\Console\Command;

use Dudenkoff\MVVMLearn\Model\BookFactory;
use Dudenkoff\MVVMLearn\Model\ResourceModel\Book as BookResource;
use Dudenkoff\MVVMLearn\Model\ResourceModel\Book\CollectionFactory;
use Dudenkoff\MVVMLearn\Api\BookRepositoryInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BookExamplesCommand extends Command
{
    const OPTION_EXAMPLE = 'example';

    /**
     * @var BookFactory
     */
    private $bookFactory;

    /**
     * @var BookResource
     */
    private $bookResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var BookRepositoryInterface
     */
    private $bookRepository;

    /**
     * @param BookFactory $bookFactory
     * @param BookResource $bookResource
     * @param CollectionFactory $collectionFactory
     * @param BookRepositoryInterface $bookRepository
     * @param string|null $name
     */
    public function __construct(
        BookFactory $bookFactory,
        BookResource $bookResource,
        CollectionFactory $collectionFactory,
        BookRepositoryInterface $bookRepository,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->bookFactory = $bookFactory;
        $this->bookResource = $bookResource;
        $this->collectionFactory = $collectionFactory;
        $this->bookRepository = $bookRepository;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('dudenkoff:mvvm:book-examples');
        $this->setDescription('Demonstrate Model, ResourceModel, Collection, and Repository patterns');
        $this->addOption(
            self::OPTION_EXAMPLE,
            'e',
            InputOption::VALUE_REQUIRED,
            'Example: model, resource, collection, repository, all',
            'all'
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $example = $input->getOption(self::OPTION_EXAMPLE);

        $output->writeln('');
        $output->writeln('<info>=== MAGENTO MVC/MVVM PATTERNS ===</info>');
        $output->writeln('');

        switch ($example) {
            case 'model':
                $this->demonstrateModel($output);
                break;
            case 'resource':
                $this->demonstrateResourceModel($output);
                break;
            case 'collection':
                $this->demonstrateCollection($output);
                break;
            case 'repository':
                $this->demonstrateRepository($output);
                break;
            case 'all':
                $this->demonstrateModel($output);
                $this->demonstrateResourceModel($output);
                $this->demonstrateCollection($output);
                $this->demonstrateRepository($output);
                break;
            default:
                $output->writeln('<error>Invalid example. Use: model, resource, collection, repository, or all</error>');
                return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Demonstrate Model pattern
     */
    private function demonstrateModel(OutputInterface $output)
    {
        $output->writeln('<comment>1. MODEL Pattern (ORM)</comment>');
        $output->writeln('   Purpose: Represents a single entity with business logic');
        $output->writeln('');

        // Load a book
        $book = $this->bookFactory->create();
        $this->bookResource->load($book, 1);

        $output->writeln("   Loaded: {$book->getTitle()} by {$book->getAuthor()}");
        $output->writeln("   Price: \${$book->getPrice()}");
        $output->writeln("   In Stock: " . ($book->isInStock() ? 'Yes' : 'No'));
        
        // Business logic example
        $discounted = $book->getDiscountedPrice(20);
        $output->writeln("   20% Discount: \${$discounted}");
        $output->writeln('');
    }

    /**
     * Demonstrate ResourceModel pattern
     */
    private function demonstrateResourceModel(OutputInterface $output)
    {
        $output->writeln('<comment>2. RESOURCE MODEL Pattern (Database Operations)</comment>');
        $output->writeln('   Purpose: Direct database access with custom SQL');
        $output->writeln('');

        // Custom query
        $books = $this->bookResource->getBooksByAuthor('Jane');
        $output->writeln("   Books by 'Jane': " . count($books));

        // Aggregate query
        $totalValue = $this->bookResource->getTotalInventoryValue();
        $output->writeln("   Total Inventory Value: \${$totalValue}");
        $output->writeln('');
    }

    /**
     * Demonstrate Collection pattern
     */
    private function demonstrateCollection(OutputInterface $output)
    {
        $output->writeln('<comment>3. COLLECTION Pattern (Multiple Records)</comment>');
        $output->writeln('   Purpose: Work with sets of records, filtering, sorting');
        $output->writeln('');

        // Get in-stock books
        $collection = $this->collectionFactory->create();
        $collection->addStatusFilter(1)
                   ->addInStockFilter()
                   ->setOrderByTitle('ASC');

        $output->writeln("   In-Stock Books ({$collection->getSize()}):");
        foreach ($collection as $book) {
            $output->writeln("   - {$book->getTitle()} (\${$book->getPrice()})");
        }
        $output->writeln('');

        // Price filter example
        $expensiveBooks = $this->collectionFactory->create();
        $expensiveBooks->addPriceFilter(40)
                       ->setOrderByTitle('ASC');

        $output->writeln("   Books over \$40 ({$expensiveBooks->getSize()}):");
        foreach ($expensiveBooks as $book) {
            $output->writeln("   - {$book->getTitle()} (\${$book->getPrice()})");
        }
        $output->writeln('');
    }

    /**
     * Demonstrate Repository pattern
     */
    private function demonstrateRepository(OutputInterface $output)
    {
        $output->writeln('<comment>4. REPOSITORY Pattern (Service Contract)</comment>');
        $output->writeln('   Purpose: API-safe data access, best practice for services');
        $output->writeln('');

        try {
            // Get by ID
            $book = $this->bookRepository->getById(1);
            $output->writeln("   getById(1): {$book->getTitle()}");

            // Get by ISBN
            $book = $this->bookRepository->getByIsbn('978-1234567890');
            $output->writeln("   getByIsbn('978-1234567890'): {$book->getTitle()}");

            $output->writeln('');
            $output->writeln('   ✓ Repository provides type-safe, API-compatible access');
        } catch (\Exception $e) {
            $output->writeln("   <error>Error: {$e->getMessage()}</error>");
        }
        $output->writeln('');
    }
}

