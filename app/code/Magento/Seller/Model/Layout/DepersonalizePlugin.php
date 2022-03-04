<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Layout;

use Magento\Seller\Model\SellerFactory;
use Magento\Seller\Model\Session as SellerSession;
use Magento\Seller\Model\Visitor;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Depersonalize seller data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    private $depersonalizeChecker;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var SellerSession
     */
    private $sellerSession;

    /**
     * @var SellerFactory
     */
    private $sellerFactory;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var int
     */
    private $sellerGroupId;

    /**
     * @var string
     */
    private $formKey;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param SessionManagerInterface $session
     * @param SellerSession $sellerSession
     * @param SellerFactory $sellerFactory
     * @param Visitor $visitor
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        SessionManagerInterface $session,
        SellerSession $sellerSession,
        SellerFactory $sellerFactory,
        Visitor $visitor
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->session = $session;
        $this->sellerSession = $sellerSession;
        $this->sellerFactory = $sellerFactory;
        $this->visitor = $visitor;
    }

    /**
     * Retrieve sensitive seller data.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function beforeGenerateXml(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->sellerGroupId = $this->sellerSession->getSellerGroupId();
            $this->formKey = $this->session->getData(FormKey::FORM_KEY);
        }
    }

    /**
     * Change sensitive seller data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->visitor->setSkipRequestLogging(true);
            $this->visitor->unsetData();
            $this->session->clearStorage();
            $this->sellerSession->clearStorage();
            $this->session->setData(FormKey::FORM_KEY, $this->formKey);
            $this->sellerSession->setSellerGroupId($this->sellerGroupId);
            $this->sellerSession->setSeller($this->sellerFactory->create()->setGroupId($this->sellerGroupId));
        }
    }
}
