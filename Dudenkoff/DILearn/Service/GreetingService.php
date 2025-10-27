<?php
/**
 * Greeting Service
 * 
 * DEMONSTRATES:
 * 1. Constructor Dependency Injection
 * 2. Injecting interfaces (not concrete classes)
 * 3. Injecting primitive values via di.xml
 * 4. Using Factory pattern
 * 5. Being intercepted by Plugins
 * 
 * KEY CONCEPTS:
 * - Dependencies are declared in constructor
 * - Type-hint interfaces when possible
 * - Magento's Object Manager handles injection automatically
 * - Values can be configured in di.xml
 */

namespace Dudenkoff\DILearn\Service;

use Dudenkoff\DILearn\Api\LoggerInterface;
use Dudenkoff\DILearn\Model\MessageFactory;

class GreetingService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var string
     */
    private $defaultGreeting;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @var array
     */
    private $allowedLanguages;

    /**
     * Constructor - Dependencies Injected Here
     * 
     * IMPORTANT RULES:
     * 1. Never call ObjectManager::getInstance() in constructor
     * 2. Type-hint interfaces, not implementations
     * 3. Primitive types can have default values or be injected via di.xml
     * 4. All dependencies must be in constructor (not in methods)
     *
     * @param LoggerInterface $logger Injected based on preference in di.xml
     * @param MessageFactory $messageFactory Auto-generated factory
     * @param string $defaultGreeting Injected from di.xml
     * @param bool $isEnabled Injected from di.xml
     * @param int $maxRetries Injected from di.xml
     * @param array $allowedLanguages Injected from di.xml
     */
    public function __construct(
        LoggerInterface $logger,
        MessageFactory $messageFactory,
        string $defaultGreeting = 'Hello',
        bool $isEnabled = true,
        int $maxRetries = 1,
        array $allowedLanguages = []
    ) {
        $this->logger = $logger;
        $this->messageFactory = $messageFactory;
        $this->defaultGreeting = $defaultGreeting;
        $this->isEnabled = $isEnabled;
        $this->maxRetries = $maxRetries;
        $this->allowedLanguages = $allowedLanguages;
    }

    /**
     * Greet a person
     * 
     * NOTE: This method will be intercepted by GreetingLoggerPlugin
     *
     * @param string $name
     * @return string
     */
    public function greet(string $name): string
    {
        if (!$this->isEnabled) {
            return 'Service is disabled';
        }

        $greeting = $this->defaultGreeting . ', ' . $name . '!';
        
        // Use the injected logger
        $this->logger->log('Greeting: ' . $greeting);

        // Use the factory to create a Message instance
        $message = $this->messageFactory->create([
            'data' => [
                'text' => $greeting,
                'author' => 'GreetingService'
            ]
        ]);

        return $message->getFormatted();
    }

    /**
     * Get configuration info
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'default_greeting' => $this->defaultGreeting,
            'is_enabled' => $this->isEnabled,
            'max_retries' => $this->maxRetries,
            'allowed_languages' => $this->allowedLanguages
        ];
    }

    /**
     * Get logged messages
     *
     * @return array
     */
    public function getLoggedMessages(): array
    {
        return $this->logger->getMessages();
    }
}

