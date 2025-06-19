<!-- Pagination.vue -->
<template>
    <nav class="pagination-fixed">
        <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button
                    class="page-link"
                    @click="$emit('page-change', currentPage - 1)"
                >
                    Trước
                </button>
            </li>

            <li
                class="page-item"
                v-for="page in totalPages"
                :key="page"
                :class="{ active: currentPage === page }"
            >
                <button class="page-link" @click="$emit('page-change', page)">
                    {{ page }}
                </button>
            </li>

            <li
                class="page-item"
                :class="{ disabled: currentPage === totalPages }"
            >
                <button
                    class="page-link"
                    @click="$emit('page-change', currentPage + 1)"
                >
                    Sau
                </button>
            </li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: "Pagination",
    props: {
        currentPage: { type: Number, required: true },
        totalPages: { type: Number, required: true },
    },
};
</script>

<style>
.pagination-fixed {
    position: fixed;
    bottom: 220px;
    right: 20px;
    z-index: 1000;
    background-color: #fff;
    padding: 8px 12px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style>
