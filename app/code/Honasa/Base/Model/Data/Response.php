<?php
namespace Honasa\Base\Model\Data;

use \Magento\Framework\Model\AbstractModel;

/**
 * Interface Response
 *
 */
class Response  implements \Honasa\Base\Api\Data\ResponseInterface
{   
    public function __construct()
    {   $this->status = false;
        $this->message = '';
        $this->resource = '';
        $this->data = [];
    }
    /**
     * @inheritDoc
     *
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     *
     */
    public function setMessage($message)
    {   
        $this->message = $message;
    }

    /**
     * @inheritDoc
     *
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     *
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @inheritDoc
     *
     */
    public function getData()
    {   
        return  $this->data;
    }

    /**
     * @inheritDoc
     * 
     */
    public function setData($data)
    {   
        $this->data = $data;


    }

    /**
     * @inheritDoc
     *
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @inheritDoc
     *
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

}