<?php

class Pagination {

    public $buttons = [];
    public $result = [];

    public function __construct( $itemsCount = 100, $itemsPerPage = 5, $currentPage = 1, $showButtons = 5)
    {
        $pagesCount = ceil($itemsCount / $itemsPerPage);

        if ($currentPage > $pagesCount) {
            $currentPage = $pagesCount;
        }
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        
        $itemsStart = ($currentPage - 1) * $itemsPerPage + 1 ;
        $itemsEnd = $itemsStart + $itemsPerPage - 1;
        if ($itemsEnd > $itemsCount) {
            $itemsEnd = $itemsCount;
        }
        
        $result = ['itemsCount' => $itemsCount, 'pagesCount' => $pagesCount, 'currentPage' => $currentPage, 'itemsStart' => $itemsStart, 'itemsEnd' => $itemsEnd];
       
        $this->result = $result;
    }
}