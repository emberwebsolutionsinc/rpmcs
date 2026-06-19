<SearchToolbar
    v-model:search="filters.search"
    v-model:per-page="filters.per_page"
    @search="searchProjects"
    @create="openCreateModal"
/>