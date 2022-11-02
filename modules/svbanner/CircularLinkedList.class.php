<?php
/**
 * @class  CircularLinkedList
 * @author singleview(root@singleview.co.kr)
 * @brief  https://stackoverflow.com/questions/13252536/php-circular-linked-list-implementation
 */

class Node
{
    public $oBannerInfo;
    public $oLink;
    
    function __construct($oBanner, $oNext = NULL)
    {
        $this->oBannerInfo = new stdClass();
        $this->oBannerInfo->nBannerSrl = $oBanner->banner_srl;
        $this->oBannerInfo->nPackageSrl = $oBanner->package_srl;
        $this->oBannerInfo->nImgFileSrl = $oBanner->img_file_srl;
        $this->oBannerInfo->sLandingUrl = $oBanner->landing_url;
        $this->oLink = $oNext;
    }
}

class CircularLinkedList
{
    private $oHead;
    private $oCurrent;
    private $nNodeCnt;
    
    function __construct()
    {
        $this->nNodeCnt   = 0;
        $this->oHead   = null;
        $this->oCurrent = null;
    }
    
    function isEmpty()
    {
        return ($this->oHead == NULL);
    }
    
    function push($data)
    {
        if ($this->isEmpty()) 
        {
            $this->oHead = new Node($data);
            $this->oCurrent = $this->oHead;
            $this->nNodeCnt++;
        } 
        else 
        {
            $this->oCurrent->oLink = new Node($data, $this->oHead);
            $this->oCurrent = $this->oCurrent->oLink;
            $this->nNodeCnt++;
        }
    }
    function reset()
    {
        $this->oCurrent = $this->oHead;
    }
    function getCurrent()
    {
        return $this->oCurrent->oBannerInfo;
    }
    function getNext()
    {
        return $this->oCurrent->oLink->oBannerInfo;
    }
    function moveNext()
    {
        $this->oCurrent = $this->oCurrent->oLink;
    }
    function getNodeCnt()
    {
        return $this->nNodeCnt;
    }
}

// test code
// $ll = new CircularLinkedList();

// $ll->push(5);
// $ll->push(6);
// $ll->push(7);
// $ll->push(8);
// $ll->push(9);
// $ll->push(10);

// $ll->reset();

// for($j = 0; $j <= 15; $j++) 
// {
//     $result = $ll->getNext();
//     echo $result . "<br />";
// }
?>