<?php
/**
 * Observer Demo Console Command
 * 
 * DEMONSTRATES: All observer patterns and event dispatching
 * 
 * RUN THIS COMMAND:
 * bin/magento dudenkoff:observer:demo
 * 
 * This command exercises all observer concepts:
 * - Dispatching custom events
 * - Multiple observers on same event
 * - Event data passing
 * - Disabled observers
 * - Before/After concepts
 * 
 * WATCH THE LOGS:
 * tail -f var/log/system.log
 * OR
 * tail -f var/log/debug.log
 */

namespace Dudenkoff\ObserverLearn\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dudenkoff\ObserverLearn\Model\EventDispatcher;
use Psr\Log\LoggerInterface;

class ObserverDemoCommand extends Command
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param EventDispatcher $eventDispatcher
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        EventDispatcher $eventDispatcher,
        LoggerInterface $logger,
        string $name = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dudenkoff:observer:demo')
            ->setDescription('Demonstrates Magento 2 Event/Observer pattern with examples');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>========================================</info>');
        $output->writeln('<info>  Magento 2 Event/Observer Demo</info>');
        $output->writeln('<info>========================================</info>');
        $output->writeln('');

        // DEMO 1: Dispatch custom event
        $output->writeln('<comment>1. Dispatching Custom Event: dudenkoff_order_processed</comment>');
        $output->writeln('   This event has 2 observers:');
        $output->writeln('   - OrderProcessedNotificationObserver');
        $output->writeln('   - OrderProcessedAnalyticsObserver');
        $this->eventDispatcher->processOrder(12345, 'completed', 'John Doe');
        $output->writeln('   ✓ Event dispatched - Check var/log/system.log for observer execution');
        $output->writeln('');

        // DEMO 2: Multiple observers in sequence
        $output->writeln('<comment>2. Multiple Observers on Same Event</comment>');
        $output->writeln('   Event: dudenkoff_demo_event');
        $output->writeln('   Observers will execute in order:');
        $output->writeln('   1) FirstObserver');
        $output->writeln('   2) SecondObserver');
        $output->writeln('   3) ThirdObserver');
        $this->eventDispatcher->demoMultipleObservers();
        $output->writeln('   ✓ Check logs to see execution order');
        $output->writeln('');

        // DEMO 3: Disabled observer
        $output->writeln('<comment>3. Disabled Observer Test</comment>');
        $output->writeln('   Event: dudenkoff_custom_event');
        $output->writeln('   This event has DisabledObserver (disabled="true" in events.xml)');
        $this->eventDispatcher->demoDisabledObserver();
        $output->writeln('   ✓ Event dispatched - DisabledObserver should NOT execute');
        $output->writeln('');

        // DEMO 4: Passing objects
        $output->writeln('<comment>4. Passing Objects in Events</comment>');
        $output->writeln('   Events can pass any data type, including objects');
        $this->eventDispatcher->demoObjectInEvent();
        $output->writeln('   ✓ Object passed to observers successfully');
        $output->writeln('');

        // Summary
        $output->writeln('<info>========================================</info>');
        $output->writeln('<info>Demo Complete!</info>');
        $output->writeln('<info>========================================</info>');
        $output->writeln('');
        $output->writeln('📝 <comment>Check your logs to see observer execution:</comment>');
        $output->writeln('   tail -f var/log/system.log');
        $output->writeln('');
        $output->writeln('📚 <comment>Study the code:</comment>');
        $output->writeln('   app/code/Dudenkoff/ObserverLearn/');
        $output->writeln('');
        $output->writeln('🔍 <comment>Key files:</comment>');
        $output->writeln('   etc/events.xml                    - Event registrations');
        $output->writeln('   Observer/*.php                     - Observer implementations');
        $output->writeln('   Model/EventDispatcher.php          - How to dispatch events');
        $output->writeln('');
        $output->writeln('📖 <comment>Documentation:</comment>');
        $output->writeln('   README.md                          - Getting started');
        $output->writeln('   OBSERVER_CONCEPTS.md               - Deep dive');
        $output->writeln('   OBSERVER_CHEATSHEET.md             - Quick reference');
        $output->writeln('');

        return Command::SUCCESS;
    }
}

