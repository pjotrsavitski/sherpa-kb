<template>
    <div>
        <b-modal ref="modal" :hide-footer="true">{{ modal.content }}</b-modal>
        <answer-create ref="createAnswerModal" :language="language" :set-translated="true"></answer-create>

        <b-input-group class="mb-2">
            <b-form-input
                id="search-text"
                v-model="search"
                type="text"
                placeholder="Search text"
                trim
                :disabled="isDisabled"
                >
            </b-form-input>
            <b-input-group-append>
                <b-button
                    variant="outline-danger"
                    @click="resetSearch"
                    :disabled="isDisabled || search == ''"
                >Reset</b-button>
            </b-input-group-append>
            <b-input-group-append>
                <b-button
                    variant="outline-secondary"
                    @click="scrollToSelected"
                    :disabled="!input.value"
                    v-b-tooltip.hover
                    title="Scroll to selected item"
                >
                    <font-awesome-icon :icon="['fas', 'eye']" />
                </b-button>
            </b-input-group-append>
            <b-input-group-append>
                <b-button
                    v-b-modal="'answer-create'"
                    variant="outline-info"
                    v-b-tooltip.hover
                    title="Add new answer"
                >
                    <font-awesome-icon :icon="['fas', 'plus']" />
                </b-button>
            </b-input-group-append>
        </b-input-group>

        <div :style="listStyle">
            <b-list-group>
                <b-list-group-item
                    v-for="option in filteredOptions"
                    :key="option.value"
                    :active="option.value==input.value"
                    class="d-flex flex-row flex-row-reverse"
                    :ref="`option-${option.value}`"
                >
                    <div class="pl-2">
                        <b-button-group size="sm">
                            <b-button
                                v-if="option.value!=value"
                                variant="success"
                                @click="setValue(option.value)"
                                :disabled="isDisabled"
                            >
                                <font-awesome-icon :icon="['fas', 'check']" />
                            </b-button>
                            <b-button
                                v-if="option.value==value"
                                variant="danger"
                                @click="removeValue"
                                :disabled="isDisabled"
                            >
                                <font-awesome-icon :icon="['fas', 'times-circle']" />
                            </b-button>
                            <b-button
                                @click="openModal(option.text)"
                                variant="secondary"
                            >
                                <font-awesome-icon :icon="['fas', 'info-circle']" />
                            </b-button>
                        </b-button-group>
                    </div>

                    <div class="flex-grow-1">
                        <small class="mb-0">
                            {{ shorten(option.text, 120) }}
                        </small>
                    </div>
                </b-list-group-item>
            </b-list-group>
        </div>
    </div>
</template>

<script>
    import AnswerCreate from '../Answer/Create.vue'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faInfoCircle, faTimesCircle, faCheck, faEye, faPlus } from '@fortawesome/free-solid-svg-icons'

    library.add(faInfoCircle, faTimesCircle, faCheck, faEye, faPlus)

    export default {
        props: ['options', 'value', 'height', 'disabled', 'language'],
        components: {
            AnswerCreate
        },
        mounted() {
            if (this.height) {
                this.listStyle['max-height'] = this.height
            }

            this.$nextTick(() => {
                this.scrollToSelected()
            })
            this.$root.$once('bv::modal::shown', this.handleModalShown)

            this.$refs.createAnswerModal.$on('answer-created', answer => {
                this.setValue(answer.id)
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.scrollToSelected()
                    }, 1000);
                })
            })
        },
        unmounted() {
            this.$off('bv::modal::shown', this.handleModalShown)
            this.$refs.createAnswerModal.$off('answer-created')
        },
        computed: {
            filteredOptions() {
                if (this.search != '') {
                    return this.options.filter(option => {
                        if (option.value == this.input.value) return true
                        return option.text.toLowerCase().includes(this.search.toLowerCase())
                    })
                }

                return this.options;
            },
            isDisabled() {
                return !!this.disabled
            }
        },
        data() {
            return {
                search: '',
                input: {
                    value: this.value
                },
                modal: {
                    content: ''
                },
                listStyle: {
                    'max-height': '300px',
                    'overflow-y': 'scroll',
                    'display': 'block'
                }
            }
        },
        methods: {
            shorten(description, length) {
                // TODO See if there is a cleaner way to proide default value
                if (!length) {
                    length = 50
                }

                // TODO It might make sense to remove newlines before checking length
                if (description && description.length > length) {
                    return `${description.substr(0, length)}...`
                }

                return description
            },
            openModal(content) {
                this.modal.content = content

                this.$refs['modal'].show()
            },
            setValue(value) {
                this.input.value = value
                this.$emit('input', this.input.value)
            },
            removeValue() {
                this.input.value = ''
                this.$emit('input', this.input.value)
            },
            scrollToSelected() {
                if (this.input.value) {
                    this.$refs[`option-${this.input.value}`][0].scrollIntoView({
                        behavior: 'smooth'
                    })
                }
            },
            resetSearch() {
                this.search = ''
                this.$nextTick(() => {
                    this.scrollToSelected()
                })
            },
            handleModalShown(bvModalEvt, modalId) {
                if ('question-edit' === modalId || 'question-review' === modalId) {
                    this.scrollToSelected()
                }
            }
        }
    }
</script>

<style scoped>
.btn.btn-outline-info:hover {
    color: #fff;
}
</style>
