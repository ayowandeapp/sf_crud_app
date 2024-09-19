<?php

namespace App\Service\Pagination;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Paginate
{
    public function __construct(private RequestStack $requestStack) {}
    public function paginate($query, $page, $limit): array
    {
        $offset = ($page - 1) * $limit;

        // Set the offset and limit on the query
        $query->setFirstResult($offset)->setMaxResults($limit);

        // Convert the query result into an array
        $data = $query->getArrayResult();

        //get total items and cal total pages 
        $paginator = new Paginator($query, true);
        $totalItems = $paginator->count();
        $totalPages = ceil($totalItems / $limit);

        // // Build the next and previous page URLs manually using $request
        $request = $this->requestStack->getCurrentRequest();

        $currentUri = $request->getPathInfo();
        $queryParams = $request->query->all();
        $queryParams['limit'] = $limit;

        // Construct the next page URL if there are more pages
        $queryParams['page'] = $page < $totalPages ? $page + 1 : $page;
        $nextPage = $page < $totalPages ? $currentUri . '?' . http_build_query($queryParams) : null;

        // Construct the previous page URL if applicable
        $queryParams['page'] = $page > 1 ? $page - 1 : $page;
        $prevPage = $page > 1 ? $currentUri . '?' . http_build_query($queryParams) : null;

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'next' => $nextPage,
                'previous' => $prevPage,
            ]
        ];
    }
}
