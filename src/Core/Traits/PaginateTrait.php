<?php

declare(strict_types=1);
namespace NazmulIslam\Utility\Core\Traits;

trait PaginateTrait {

    public string|null $searchFilter;
    public int $perPage = 25;
    public int $page = 1;
    public array|null $sortingColumns;

    public function setSearchFilter(string|null $searchFilter): static {
        $this->searchFilter = $searchFilter;

        return $this;
    }

    public function setPerPage(int $perPage): static {
        $this->perPage = $perPage;

        return $this;
    }

    public function setPage(int $page): static {
        $this->page = $page;

        return $this;
    }

    public function setSortingColumns(array|null $sortingColumns): static {
        $this->sortingColumns = $sortingColumns;

        return $this;
    }

    public function setDefaultPaginationValues(array $input): void {
        $this->setSearchFilter(searchFilter: (isset($input['filter']) ? $input['filter'] : null));
        $this->setSortingColumns(sortingColumns: (isset($input['sort']) && !empty($input['sort'])) ? explode('|', $input['sort']) : []);
        $this->setPerPage(perPage: isset($input['per_page']) ? intval($input['per_page']) : 10);
        $this->setPage(page: isset($input['page']) ? intval($input['page']) : 1);
    }

}
