<template>
    <div>
        <topic-create></topic-create>
        <topic-edit :topic="topic" v-if="topic"></topic-edit>

        <h3>
            Categories
            <b-spinner type="grow" label="Loading categories data" small variant="secondary" v-if="isLoadingData"></b-spinner>
        </h3>

        <table-search-descriptions></table-search-descriptions>

        <b-button
            v-b-modal="'topic-create'"
            variant="primary"
            class="mb-4"
            v-b-tooltip
            title="Add new category"
        >
            <font-awesome-icon :icon="['fas', 'plus']" />
        </b-button>

        <b-table
            striped
            hover
            :fields="fields"
            :items="filteredItems"
            primary-key="id"
            thead-class="text-center"
            stacked="lg"
            :per-page="perPage"
            :current-page="currentPage"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            :busy="isBusy"
        >
            <template v-slot:cell(id)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(description)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(created_at)="data">
                {{ formatDate(data.value) }}
            </template>

            <template v-slot:cell(updated_at)="data">
                {{ formatDate(data.value) }}
            </template>

            <template v-slot:cell(actions)="data">
                <b-button-group>
                    <b-button
                        variant="success"
                        @click="onOpenModal(data.item)"
                        v-b-tooltip
                        title="Edit"
                    >
                        <font-awesome-icon :icon="['fas', 'edit']" />
                    </b-button>
                    <b-button
                        variant="danger"
                        @click="showDeleteConfirmation(data.item)"
                        v-b-tooltip
                        title="Delete"
                    >
                        <font-awesome-icon :icon="['fas', 'trash']" />
                    </b-button>
                </b-button-group>
            </template>
        </b-table>

        <b-pagination
            v-model="currentPage"
            :total-rows="totalRows"
            :per-page="perPage"
            align="center"
            v-if="totalRows > perPage"
        ></b-pagination>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import TopicCreate from './Create.vue'
import TopicEdit from './Edit.vue'
import TableSearchDescriptions from '../TableSearchDescriptions.vue'
import TableHelpers from '../../mixins/TableHelpers'
import TableSearchHelpers from '../../mixins/TableSearchHelpers'
import ToastHelpers from '../../mixins/ToastHelpers'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faPlus, faEdit, faTrash } from '@fortawesome/free-solid-svg-icons'

library.add(faPlus, faEdit, faTrash)

export default {
    props: ['items', 'isBusy'],
    mixins: [TableHelpers, TableSearchHelpers, ToastHelpers],
    components: {
        TopicCreate,
        TopicEdit,
        TableSearchDescriptions
    },
    computed: {
        filteredItems() {
            // Override search method
            if (this.form.search !== '') {
                return this.items.filter(item => {
                    return item.description.toLowerCase().includes(this.form.search.toLowerCase())
                })
            }

            return this.items
        },
        ...mapState({
            perPage: state => state.app.itemsPerPage
        }),
        fields() {
            const fields = [
                {
                    key: 'id',
                    label: 'ID',
                    sortable: true,
                    tdClass: ['align-middle', 'text-center']
                },
                {
                    key: 'description',
                    sortable: false,
                    tdClass: ['align-middle', 'text-center']
                },
                {
                    key: 'created_at',
                    sortable: false,
                    tdClass: ['align-middle', 'text-center']
                },
                {
                    key: 'updated_at',
                    sortable: false,
                    tdClass: ['align-middle', 'text-center']
                },
                {
                    key: 'actions',
                    label: '',
                    sortable: false,
                    tdClass: ['align-middle', 'text-right']
                }
            ]

            return fields
        },
        modalId() {
            const type = 'edit'
            return `topic-${type}`
        },
    },
    data() {
        return {
            currentPage: 1,
            sortBy: 'id',
            sortDesc: true,
            topic: null,
            isLoadingData: false
        }
    },
    methods: {
        onOpenModal(topic) {
            this.topic = topic
            this.$nextTick(() => {
                this.$bvModal.show(this.modalId)
            })
        },
        showDeleteConfirmation(topic) {
            this.$bvModal.msgBoxConfirm(`Are you sure you want to delete category with ID of ${topic.id}?`,
                {
                    title: 'Please confirm',
                    size: 'sm',
                    buttonSize: 'sm',
                    okVariant: 'danger',
                    okTitle: 'Confirm',
                    cancelTitle: 'Cancel',
                    footerClass: 'p-2',
                    hideHeaderClose: false,
                    centered: true
                })
                .then(value => {
                    if (value) {
                        this.$store.dispatch('topics/deleteTopic', topic)
                        .catch(err => {
                            console.error(err)
                            this.displayHttpError(err)
                        })
                    }
                })
                .catch(err => {
                    console.error('Delete topic confirmation dialog error', err)
                })
        }
    },
    created() {
        this.isLoadingData = true;
        this.$store.dispatch('topics/loadAllTopics')
            .then(() => {
                this.isLoadingData = false
            })
            .catch(error => {
                this.isLoadingData = false
                console.error(error)
            })
    }
}
</script>

<style scoped>
h3 > .spinner-grow {
    vertical-align: middle;
}
</style>
