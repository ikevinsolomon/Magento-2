<?php
namespace Honasa\Base\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Enqueue\Sqs\SqsConnectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;
use Honasa\Base\Helper\Constants;

class EventPublisher extends AbstractHelper {
    public function __construct(SqsConnectionFactory $sqsConnectionFactory, EncryptorInterface $encryptor, LoggerInterface $logger, Constants $constants) {
        $this->sqsConnectionFactory = $sqsConnectionFactory;
        $this->encryptor = $encryptor;
        $this->constants = $constants;
        $this->logger = $logger;
    }

    private function decryptValue($key) {
        return $this->encryptor->decrypt($key);
    }

    public function sendMessage($queueName, $fifo = true, $contentBasedDeduplication = true, $message = "", $messageGroupId = "") {
        try {
            $key = $this->decryptValue($this->constants->getSQSKey());
            $secret = $this->decryptValue($this->constants->getSQSSecret());
            $region = $this->constants->getSQSRegion();
            $factory = new SqsConnectionFactory('sqs:?key=' . $key . '&secret=' . $secret . '&region=' . $region);
            $context = $factory->createContext();
            $queue = $context->createQueue($queueName);
            $queue->setFifoQueue($fifo);
            $queue->setContentBasedDeduplication($contentBasedDeduplication);
            $message = $context->createMessage($message);
            $message->setMessageGroupId($messageGroupId);
            $context->createProducer()->send($queue, $message);
        } catch(Exception $e) {
            $this->logger->debug('LOGGING EXCEPTION', ['error' => $e]);
        }
    }

}