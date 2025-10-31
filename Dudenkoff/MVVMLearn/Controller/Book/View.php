<?php
/**
 * Copyright © Dudenkoff. All rights reserved.
 * Frontend Controller - Book Detail Page
 * 
 * URL: /books/book/view/id/1
 */

namespace Dudenkoff\MVVMLearn\Controller\Book;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Dudenkoff\MVVMLearn\Api\BookRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var BookRepositoryInterface
     */
    private $bookRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param BookRepositoryInterface $bookRepository
     * @param Registry $registry
     */
    public function __construct(
        PageFactory $pageFactory,
        RequestInterface $request,
        RedirectFactory $redirectFactory,
        BookRepositoryInterface $bookRepository,
        Registry $registry
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
        $this->redirectFactory = $redirectFactory;
        $this->bookRepository = $bookRepository;
        $this->registry = $registry;
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

        try {
            $book = $this->bookRepository->getById($bookId);
            $this->registry->register('current_book', $book);
            
            $page = $this->pageFactory->create();
            $page->getConfig()->getTitle()->set($book->getTitle());
            
            return $page;
        } catch (NoSuchEntityException $e) {
            $redirect = $this->redirectFactory->create();
            return $redirect->setPath('books');
        }
    }
}

