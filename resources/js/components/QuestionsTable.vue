<template>
    <div>
        <h3>Questions</h3>
        
        <b-table
            striped
            hover
            :fields="fields"
            :items="items"
            primary-key="id"
            thead-class="text-center"
            stacked="lg"
        >
            <template v-slot:cell(id)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(description)="data">
                <b-button v-b-modal="editModalId(data.item.id)" variant="link">{{ data.item.descriptions[language] }}</b-button>
            </template>

            <template v-slot:cell(english_translation)="data">
                <b-button v-b-modal="editModalId(data.item.id)" variant="link">{{ data.item.descriptions.en }}</b-button>
            </template>

            <template v-slot:cell(category)="data">
                {{ data.item.category ? data.item.category.value : null }}
            </template>

            <template v-slot:cell(date)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(status)="data">
                {{ data.item.status.status }}
            </template>
        </b-table>
    </div>
</template>

<script>
    export default {
        props: ['items', 'language'],
        data() {
            const vm = this

            return {
                fields: [
                    {
                        key: 'id',
                        label: 'ID',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'description',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'english_translation',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'category',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'date',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'status',
                        sortable: false,
                        tdClass: (value, key, item) => {
                            let classes = ['align-middle', 'text-center']

                            switch(value.value) {
                                case 'in_translation':
                                    classes.push('table-warning')
                                    break
                                case 'translated':
                                    classes.push('table-info')
                                    break
                                case 'published':
                                    classes.push('table-success')
                            }

                            return classes
                        },
                    }
                ]
            }
        },
        methods: {
            editModalId(id) {
                return 'question-edit-' + id
            }
        }
    }
</script>
