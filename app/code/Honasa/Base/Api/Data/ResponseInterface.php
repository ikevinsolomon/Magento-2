<?php
namespace Honasa\Base\Api\Data;

/**
 * Interface ResponseInterface
 *
 */
interface ResponseInterface
{

    const STATUS = 'status';
    const MESSAGE = 'message';
    const DATA = 'data';
    const RESOURCE = 'resource';
    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     * @return void
     */
    public function setMessage($message);

    /**
     * @return bool
     */
    public function getStatus();

    /**
     * @param bool $status
     * @return void
     */
    public function setStatus($status);

    /**
     *
     * @return $this
     */
    public function getData();

    /**
     * @param array $data
     * @return void
     */
    public function setData($data);

    /**
     * 
     * @return string
     *
     */
    public function getResource();

    /**
     * 
     * @param string $resource
     * @return void
     *
     */
    public function setResource($resource);

}
