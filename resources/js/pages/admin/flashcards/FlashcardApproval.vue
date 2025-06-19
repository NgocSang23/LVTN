<template>
    <div class="container mt-4">
        <h3>Kiểm duyệt flashcard</h3>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tiêu đề</th>
                    <th>Nội dung</th>
                    <th>Người tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(card, index) in paginatedFlashcards" :key="card.id">
                    <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>
                    <td>{{ card.title }}</td>
                    <td>{{ card.content }}</td>
                    <td>{{ card.author }}</td>
                    <td>
                        <button class="btn btn-success btn-sm">Duyệt</button>
                        <button class="btn btn-danger btn-sm mx-2">Xoá</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <Pagination
            :total-pages="totalPages"
            :current-page="currentPage"
            @page-change="changePage"
        />
    </div>
</template>

<script>
import Pagination from "../../../components/Pagination.vue";
// Kiểm duyệt flashcard
export default {
    name: "FlashcardModeration",
    components: {
        Pagination,
    },
    data() {
        return {
            flashcards: [
                {
                    id: 1,
                    title: "Từ vựng 1",
                    content: "apple - quả táo",
                    author: "Nguyễn A",
                },
                {
                    id: 2,
                    title: "Từ vựng 2",
                    content: "banana - quả chuối",
                    author: "Trần B",
                },
                {
                    id: 3,
                    title: "Từ vựng 3",
                    content: "orange - quả cơ",
                    author: "Lê C",
                },
                {
                    id: 4,
                    title: "Từ vựng 4",
                    content: "grape - quả cơ",
                    author: "Phạm D",
                },
                {
                    id: 5,
                    title: "Từ vựng 5",
                    content: "mango - quả cơ",
                    author: "Đỗ E",
                },
                {
                    id: 6,
                    title: "Từ vựng 6",
                    content: "cherry - quả cơ",
                    author: "Lý F",
                },
                {
                    id: 7,
                    title: "Từ vựng 7",
                    content: "kiwi - quả cơ",
                    author: "Ngô G",
                },
                {
                    id: 8,
                    title: "Từ vựng 8",
                    content: "lemon - quả cơ",
                    author: "Hoàng H",
                },
                {
                    id: 9,
                    title: "Từ vựng 9",
                    content: "grapefruit - quả cơ",
                    author: "Trần I",
                },
                {
                    id: 10,
                    title: "Từ vựng 10",
                    content: "watermelon - quả cơ",
                    author: "Lê J",
                },
            ],
            currentPage: 1,
            perPage: 8,
        };
    },
    computed: {
        totalPages() {
            return Math.ceil(this.flashcards.length / this.perPage);
        },
        paginatedFlashcards() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.flashcards.slice(start, start + this.perPage);
        },
    },
    methods: {
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
    },
};
</script>

<style scoped>
.pagination-fixed {
    position: fixed;
    bottom: 80px;
    right: 20px;
    z-index: 1000;
    background-color: #fff;
    padding: 8px 12px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style>
