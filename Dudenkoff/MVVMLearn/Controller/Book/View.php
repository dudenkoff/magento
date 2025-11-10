<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Frontend Controller - Book Detail Page
 * 
 * URL: /books/book/view/id/1
 * 
 * NOTE: Controller only validates ID param exists.
 * Block handles data loading and page title.
 */

namespace Dudenkoff\MVVMLearn\Controller\Book;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;

class View implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        PageFactory $pageFactory,
        RequestInterface $request,
        RedirectFactory $redirectFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $bookId = (int)$this->request->getParam('id');

        if (!$bookId) {
            $redirect = $this->redirectFactory->create();
            return $redirect->setPath('books');
        }

        return $this->pageFactory->create();
    }
}

