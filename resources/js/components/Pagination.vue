<template>
    <nav class="pagination-wrapper">
        <ul class="pagination mb-0">
            <!-- Nút "Trước" -->
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button
                    class="page-link"
                    @click="$emit('page-change', currentPage - 1)"
                >
                    «
                </button>
            </li>

            <!-- Hiển thị 3 trang gần currentPage -->
            <li
                v-for="page in visiblePages"
                :key="page"
                class="page-item"
                :class="{ active: currentPage === page }"
            >
                <button class="page-link" @click="$emit('page-change', page)">
                    {{ page }}
                </button>
            </li>

            <!-- Nút "Sau" -->
            <li
                class="page-item"
                :class="{ disabled: currentPage === totalPages }"
            >
                <button
                    class="page-link"
                    @click="$emit('page-change', currentPage + 1)"
                >
                    »
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
    computed: {
        // Hiển thị tối đa 5 trang gần currentPage (giữa: currentPage ± 2)
        visiblePages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
    },
};
</script>

<style scoped>
.pagination-wrapper {
    position: fixed;
    bottom: 80px;
    right: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    padding: 4px 12px;
    z-index: 999;
}

.page-link {
    border: none;
    background-color: transparent;
    color: #495057;
}

.page-item.active .page-link {
    background-color: #0d6efd;
    color: white;
    border-radius: 8px;
    font-weight: bold;
}

.page-item.disabled .page-link {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
