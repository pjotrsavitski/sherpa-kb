<template>
   <b-form
        inline
        @submit="onSearchSubmit"
        @reset="onSearchReset"
        class="float-right mb-2"
    >
        <b-form-group
            id="input-group-search-text"
        >
            <b-input-group>
                <b-form-input
                    id="search-text"
                    v-model="$parent.form.search"
                    type="text"
                    placeholder="Search text"
                    trim
                    debounce="500"
                >
                </b-form-input>
                <b-input-group-append>
                    <b-button type="reset" variant="outline-danger">Reset</b-button>
                </b-input-group-append>
            </b-input-group>
        </b-form-group>
    </b-form>
</template>

<script>
    export default {
        methods: {
            onSearchSubmit(event) {
                event.preventDefault()
            },
            onSearchReset(event) {
                event.preventDefault()
                this.$parent.form.search = ''
            }
        }
    }
</script>
