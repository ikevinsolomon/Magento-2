<?php
namespace Honasa\Base\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Enqueue\Sqs\SqsConnectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;
use Honasa\Base\Helper\Constants;
use Honasa\Base\Helper\EventPublisher;

class CatalogEventHelper extends AbstractHelper {
    public function __construct(
        SqsConnectionFactory $sqsConnectionFactory,
        EncryptorInterface $encryptor,
        LoggerInterface $logger, 
        Constants $constants,
        EventPublisher $eventPublisher
        ) {
        $this->sqsConnectionFactory = $sqsConnectionFactory;
        $this->encryptor = $encryptor;
        $this->constants = $constants;
        $this->logger = $logger;
        $this->eventPublisher = $eventPublisher;
    }

   


    public function sendToCatalogQueue($data) {
        try {
            $message = json_encode($data);
            $queue = $this->constants->getSQSCatalogQueue();
            $catalogMessageGroupId = $this->constants->getSQSCatalogMessageGroupId();
            $this->eventPublisher->sendMessage($queue, true, true, $message, $catalogMessageGroupId);
        }
        catch(Exception $e) {
            $this->logger->debug('LOGGING EXCEPTION', ['error' => $e]);
        }
    }
}