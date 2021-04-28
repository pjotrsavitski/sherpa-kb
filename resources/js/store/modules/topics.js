const state = () => ({
    items: [],
    preloaded: []
})

const getters = {
    totalCount: state => {
        return state.items.length
    },
    sortedAsc: state => {
        return state.items.sort( (a, b) => {
            return a.description.localeCompare(b.description)
        });
    }
}

const actions = {
    loadAllTopics({ commit }) {
        return axios.get('/topics')
            .then(response => {
                commit('setTopics', response.data.data)
            })
            .catch(error => {
                console.error('Topics loading:', error)
            })
    },
    preloadAllTopics({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('items')) {
            commit('setPreloaded', 'items')
            dispatch('loadAllTopics')
        }
    },
    updateTopic({ commit }, topic) {
        commit('updateTopic', topic)
    },
    insertTopic({ commit }, topic) {
        commit('insertTopic', topic)
    },
    deleteTopic({ commit }, topic) {
        commit('deleteTopic', topic)
    }
}

const mutations = {
    setTopics (state, topics) {
        state.items = topics
    },
    updateTopic (state, topic) {
        const index = state.items.findIndex(item => {
            return item.id === topic.id
        })

        if (index !== -1) {
            Vue.set(state.items, index, topic)
        }
    },
    setPreloaded (state, value) {
        Vue.set(state.preloaded, value, value)
    },
    insertTopic(state, topic) {
        state.items.push(topic)
    },
    deleteTopic(state, topic) {
        const index = state.items.findIndex(item => {
            return item.id === topic.id
        })

        if (index !== -1) {
            state.items.splice(index, 1)
        }
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
