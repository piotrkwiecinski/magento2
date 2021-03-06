<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\Shipping
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $layout;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->context = $this->getMock(\Magento\Framework\View\Element\Template\Context::class, [], [], '', false);
        $this->customerSession = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->checkoutSession = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->configProvider = $this->getMock(
            \Magento\Checkout\Model\CompositeConfigProvider::class,
            [],
            [],
            '',
            false
        );
        $this->layoutProcessor = $this->getMock(\Magento\Checkout\Block\Checkout\LayoutProcessorInterface::class);
        $this->layout = [
            'components' => [
                'firstComponent' => ['param' => 'value'],
                'secondComponent' => ['param' => 'value'],
            ]
        ];

        $this->storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->context->expects($this->once())->method('getStoreManager')->willReturn($this->storeManager);
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\Serializer\Json::class, [], [], '', false);

        $this->model = new \Magento\Checkout\Block\Cart\Shipping(
            $this->context,
            $this->customerSession,
            $this->checkoutSession,
            $this->configProvider,
            [$this->layoutProcessor],
            ['jsLayout' => $this->layout],
            $this->serializer
        );
    }

    public function testGetCheckoutConfig()
    {
        $config = ['param' => 'value'];
        $this->configProvider->expects($this->once())->method('getConfig')->willReturn($config);
        $this->assertEquals($config, $this->model->getCheckoutConfig());
    }

    public function testGetJsLayout()
    {
        $layoutProcessed = $this->layout;
        $layoutProcessed['components']['thirdComponent'] = ['param' => 'value'];
        $jsonLayoutProcessed = json_encode($layoutProcessed);

        $this->layoutProcessor->expects($this->once())
            ->method('process')
            ->with($this->layout)
            ->willReturn($layoutProcessed);

        $this->serializer->expects($this->once())->method('serialize')->will(
            $this->returnValue($jsonLayoutProcessed)
        );
        $this->assertEquals(
            $jsonLayoutProcessed,
            $this->model->getJsLayout()
        );
    }

    public function testGetBaseUrl()
    {
        $baseUrl = 'baseUrl';
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, ['getBaseUrl'], [], '', false);
        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $this->assertEquals($baseUrl, $this->model->getBaseUrl());
    }

    public function testGetSerializedCheckoutConfig()
    {
        $checkoutConfig = ['checkout', 'config'];
        $this->configProvider->expects($this->once())->method('getConfig')->willReturn($checkoutConfig);
        $this->serializer->expects($this->once())->method('serialize')->will(
            $this->returnValue(json_encode($checkoutConfig))
        );

        $this->assertEquals(json_encode($checkoutConfig), $this->model->getSerializedCheckoutConfig());
    }
}
