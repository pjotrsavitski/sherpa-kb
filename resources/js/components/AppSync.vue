<template>
    <div class="float-right">
        <b-button
            v-b-tooltip
            title="Reload all application data"
            variant="outline-secondary"
            size="sm"
            @click="onReload()"
            v-if="!isActive"
            :disabled="isBusy"
        >
            <font-awesome-icon :icon="['fas', 'sync']" v-if="!isBusy"/>
            <b-spinner
                label="Loading"
                type="grow"
                variant="secondary"
                :small="true"
                v-if="isBusy"
            ></b-spinner>
        </b-button>
        <b-button
            title="Live-updates are active"
            v-b-tooltip
            variant="light"
            size="sm"
            v-if="isActive"
        >
            <font-awesome-icon :icon="['fas', 'globe']" class="text-success" />
        </b-button>
    </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core'
import { faSync, faGlobe } from '@fortawesome/free-solid-svg-icons'

library.add(faSync, faGlobe)

export default {
    name: "AppSync",
    props: ['isActive'],
    data() {
        return {
            isBusy: false
        }
    },
    methods: {
        onReload() {
            this.isBusy = true

            Promise.allSettled([
                this.$store.dispatch('answers/loadAllAnswers'),
                this.$store.dispatch('questions/loadAllQuestions'),
                this.$store.dispatch('pendingQuestions/loadAllPendingQuestions'),
                this.$store.dispatch('topics/loadAllTopics')
            ])
            .then(() => {
                this.isBusy = false
            })
        }
    }
}
</script>
