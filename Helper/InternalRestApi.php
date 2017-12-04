<?php

namespace Experius\InternalApi\Helper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Framework\Webapi\Rest\Request as RestRequest;

class InternalRestApi
{

    private $paramsOverrider;

    private $serviceInputProcessor;

    private $serviceOutputProcessor;

    private $objectManager;

    private $request;

    private $router;

    private $route;

    public function __construct(
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $serviceInputProcessor,
        ServiceOutputProcessor $serviceOutputProcessor,
        ObjectManagerInterface $objectManager,
        Request $request,
        Router $router
    ) {
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->router = $router;
    }

    public function call($endPoint, $httpMethod = 'GET', $payLoad = [])
    {
        $request = $this->request;
        $request->setMethod(strtoupper($httpMethod));
        $request->setPathInfo($endPoint);

        $route = $this->getRoute($request);

        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();

        if ($this->request->getHttpMethod() == RestRequest::HTTP_METHOD_PUT) {
            $inputData = $this->paramsOverrider->overrideRequestBodyIdWithPathParam(
                $this->request->getParams(),
                $payLoad,
                $serviceClassName,
                $serviceMethodName
            );
            $inputData = array_merge($inputData, $request->getParams());
        } else {
            $inputData = $payLoad;
        }

        $inputData = $this->paramsOverrider->override($inputData, $route->getParameters());
        $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);

        $service = $this->objectManager->get($serviceClassName);

        $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
        $outputData = $this->serviceOutputProcessor->process(
            $outputData,
            $serviceClassName,
            $serviceMethodName
        );

        return $outputData;
    }

    public function getRoute($request)
    {
        if (!$this->route) {
            $this->route = $this->router->match($request);
        }
        return $this->route;
    }

}
