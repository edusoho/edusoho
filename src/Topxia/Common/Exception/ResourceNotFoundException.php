<?php
namespace Topxia\Common\Exception;

class ResourceNotFoundException extends BaseException
{
    private $resourceType;

    private $resourceId;

    public function __construct($resourceType, $resourceId, $code = 0, array $headers = array())
    {
        parent::__construct(404, sprintf('The resource of %s can not be found with ID#%s', $resourceType, $resourceId), null, $headers, $code);

        $this->resourceType = $resourceType;
        $this->resourceId   = $resourceId;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }
}
