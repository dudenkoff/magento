<?php
/**
 * Notification Service
 * 
 * DEMONSTRATES: Virtual Types
 * 
 * This service receives a "special" logger that's configured differently.
 * The logger is a Virtual Type defined in di.xml - it's BasicLogger
 * but with a different prefix argument.
 * 
 * VIRTUAL TYPE BENEFITS:
 * - Reuse existing classes with different configurations
 * - No need to create new PHP files
 * - Keeps code DRY (Don't Repeat Yourself)
 */

namespace Dudenkoff\DILearn\Service;

use Dudenkoff\DILearn\Api\LoggerInterface;

class NotificationService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     * 
     * The $logger here will be the SpecialLogger virtual type
     * (configured in di.xml)
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Send a notification
     *
     * @param string $message
     * @param string $recipient
     * @return string
     */
    public function send(string $message, string $recipient): string
    {
        $notification = "To: {$recipient} - {$message}";
        $this->logger->log($notification);
        
        return "Notification sent: {$notification}";
    }

    /**
     * Get notification history
     *
     * @return array
     */
    public function getHistory(): array
    {
        return $this->logger->getMessages();
    }
}

