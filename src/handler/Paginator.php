<?php

namespace mot\handler;

class Paginator
{
    protected $paginator;
    protected $requestPath;
    protected $requestParams;

    public function __construct(object $paginator, string $requestPath, array $requestParams)
    {
        $this->paginator = $paginator;
        $this->requestPath = $requestPath;
        $this->requestParams = $requestParams;
    }

    public function getPaginate(): array
    {
        $paginator = $this->paginator;
        $path = $this->requestPath;
        $params = $this->requestParams;

        $pageName = 'page';
        // $paginator->setPageName($pageName);
        if (isset($params[$pageName])) {
            unset($params[$pageName]);
        }
        if ($params) {
            $path .= '?' . http_build_query($params);
        }
        $paginator->withPath($path);

        $paginate = json_decode(json_encode($paginator), true);
        $lastPage = $paginate['last_page'] ?: 1;
        $currPage = $paginate['current_page'] ?: 1;
        $pages = [];
        $urls = $this->getPageUrls();
        foreach ($urls as $k => $v) {
            $pages[] = [
                'num' => $k,
                'url' => $v,
                'active' => $k == $currPage
            ];
        }

        $paginate['pages'] = $pages;

        return $paginate;
    }

    protected function getPageUrls(): array
    {
        $paginator = $this->paginator;

        $lastPage = $paginator->lastPage();
        $currPage = $paginator->currentPage();
        $showNum = 10;

        if ($lastPage <= $showNum) {
            $urls = $paginator->getUrlRange(1, $lastPage);
        } elseif ($currPage <= 6) {
            $urls1 = $paginator->getUrlRange(1, 8);
            $urls2 = $paginator->getUrlRange($lastPage - 1, $lastPage);
            $urls = array_merge($urls1, ['' => 'javascript:;'], $urls2);
        } else {
            // todo
            $urls1 = $paginator->getUrlRange(1, 2);
            $urls2 = $paginator->getUrlRange($lastPage - 8, $lastPage);
            $urls = array_merge($urls1, ['' => 'javascript:;'], $urls2);
        }

        return $urls;
    }
}
