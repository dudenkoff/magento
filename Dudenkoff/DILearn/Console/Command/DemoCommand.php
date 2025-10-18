<?php
/**
 * DI Demo Console Command
 * 
 * DEMONSTRATES: How to use all DI concepts in a practical example
 * 
 * RUN THIS COMMAND:
 * bin/magento dudenkoff:di:demo
 * 
 * This command exercises all the DI concepts:
 * - Interface injection
 * - Factory usage
 * - Virtual types
 * - Proxies
 * - Shared vs non-shared instances
 * - Plugins
 */

namespace Dudenkoff\DILearn\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Dudenkoff\DILearn\Service\GreetingService;
use Dudenkoff\DILearn\Service\NotificationService;
use Dudenkoff\DILearn\Service\HeavyService;
use Dudenkoff\DILearn\Model\Counter;
use Dudenkoff\DILearn\Model\MessageFactory;

class DemoCommand extends Command
{
    const NAME_ARGUMENT = 'name';

    /**
     * @var GreetingService
     */
    private $greetingService;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var HeavyService
     */
    private $heavyService;

    /**
     * @var Counter
     */
    private $counter1;

    /**
     * @var Counter
     */
    private $counter2;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * Constructor - All dependencies injected automatically!
     *
     * @param GreetingService $greetingService
     * @param NotificationService $notificationService
     * @param HeavyService $heavyService
     * @param Counter $counter1
     * @param Counter $counter2
     * @param MessageFactory $messageFactory
     * @param string|null $name
     */
    public function __construct(
        GreetingService $greetingService,
        NotificationService $notificationService,
        HeavyService $heavyService,
        Counter $counter1,
        Counter $counter2,
        MessageFactory $messageFactory,
        string $name = null
    ) {
        $this->greetingService = $greetingService;
        $this->notificationService = $notificationService;
        $this->heavyService = $heavyService;
        $this->counter1 = $counter1;
        $this->counter2 = $counter2;
        $this->messageFactory = $messageFactory;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dudenkoff:di:demo')
            ->setDescription('Demonstrates Dependency Injection concepts in Magento 2')
            ->addArgument(
                self::NAME_ARGUMENT,
                InputArgument::OPTIONAL,
                'Your name',
                'World'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument(self::NAME_ARGUMENT);

        $output->writeln('<info>======================================</info>');
        $output->writeln('<info>  Magento 2 Dependency Injection Demo</info>');
        $output->writeln('<info>======================================</info>');
        $output->writeln('');

        // DEMO 1: Interface Preference & Constructor Injection
        $output->writeln('<comment>1. Testing GreetingService (Interface Preference & Plugins):</comment>');
        $greeting = $this->greetingService->greet($name);
        $output->writeln("   Result: {$greeting}");
        $output->writeln('   Notice: The [Via Plugin] suffix was added by the plugin!');
        $output->writeln('');

        // Show configuration injected via di.xml
        $output->writeln('<comment>2. Configuration Injected via di.xml:</comment>');
        $config = $this->greetingService->getConfig();
        foreach ($config as $key => $value) {
            $valueStr = is_array($value) ? json_encode($value) : $value;
            $output->writeln("   {$key}: {$valueStr}");
        }
        $output->writeln('');

        // DEMO 2: Virtual Type
        $output->writeln('<comment>3. Testing NotificationService (Virtual Type):</comment>');
        $notification = $this->notificationService->send('Hello!', $name);
        $output->writeln("   {$notification}");
        $history = $this->notificationService->getHistory();
        $output->writeln('   Logger prefix: ' . (isset($history[0]) ? substr($history[0], 0, 10) : 'N/A'));
        $output->writeln('   This uses a virtual type with [SPECIAL] prefix');
        $output->writeln('');

        // DEMO 3: Shared vs Non-Shared
        $output->writeln('<comment>4. Testing Counter (Non-Shared Instances):</comment>');
        $output->writeln('   Counter is set as shared="false" in di.xml');
        $output->writeln('   Counter 1: ' . $this->counter1->increment());
        $output->writeln('   Counter 1: ' . $this->counter1->increment());
        $output->writeln('   Counter 2: ' . $this->counter2->increment());
        $output->writeln('   Counter 2: ' . $this->counter2->increment());
        $output->writeln('   Both start at 0 because they are different instances!');
        $output->writeln('');

        // DEMO 4: Factory Pattern
        $output->writeln('<comment>5. Testing Factory Pattern:</comment>');
        $message1 = $this->messageFactory->create([
            'data' => ['text' => 'First message', 'author' => 'User1']
        ]);
        $message2 = $this->messageFactory->create([
            'data' => ['text' => 'Second message', 'author' => 'User2']
        ]);
        $output->writeln('   Message 1: ' . $message1->getFormatted());
        $output->writeln('   Message 2: ' . $message2->getFormatted());
        $output->writeln('   Factory creates new instances each time');
        $output->writeln('');

        // DEMO 5: Proxy Pattern
        $output->writeln('<comment>6. Testing HeavyService (Proxy Pattern):</comment>');
        $output->writeln('   Without proxy: HeavyProcessor instantiated when HeavyService created');
        $output->writeln('   With proxy: HeavyProcessor instantiated only when method is called');
        $result = $this->heavyService->processData('test data');
        $output->writeln("   Processed: {$result}");
        $output->writeln('');

        // Show logged messages
        $output->writeln('<comment>7. Logged Messages (from injected logger):</comment>');
        $messages = $this->greetingService->getLoggedMessages();
        foreach ($messages as $msg) {
            $output->writeln("   {$msg}");
        }
        $output->writeln('');

        $output->writeln('<info>======================================</info>');
        $output->writeln('<info>Check app/code/Dudenkoff/DILearn/ for</info>');
        $output->writeln('<info>detailed comments explaining each concept!</info>');
        $output->writeln('<info>======================================</info>');

        return Command::SUCCESS;
    }
}

