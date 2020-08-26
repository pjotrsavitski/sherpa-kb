const state = () => ({
    items: [],
    states: [],
    preloaded: []
})

const getters = {
    totalCount: state => {
        return state.items.length
    },
    forLanguage: state => language => {
        return state.items.filter(item => item.descriptions.hasOwnProperty(language))
    },
    forReview: state => {
        return state.items.filter(item => item.status.value !== 'in_translation')
    }
}

const actions = {
    loadAllAnswers({ commit }) {
        return axios.get('/answers')
        .then(response => {
            commit('setAnswers', response.data.data)
        })
        .catch(error => {
            console.error('Answers loading:', error)
        })
    },
    preloadAllAnswers({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('items')) {
            commit('setPreloaded', 'items')
            dispatch('loadAllAnswers')
        }
    },
    loadStates({ commit } ) {
        return axios.get('/answers/states')
        .then(response => {
            commit('setStates', response.data)
        })
        .catch(error => {
            console.error('Answer states loading:', error)
        })
    },
    preloadStates({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('states')) {
            commit('setPreloaded', 'states')
            dispatch('loadStates')
        }
    },
    insertAnswer({ commit }, answer) {
        commit('insertAnswer', answer)
    },
    updateAnswer({ commit }, answer) {
        commit('updateAnswer', answer)
    }
}

const mutations = {
    setAnswers (state, answers) {
        state.items = answers
    },
    updateAnswer (state, answer) {
        const index = state.items.findIndex(item => {
            return item.id === answer.id
        })

        if (index !== -1) {
            Vue.set(state.items, index, answer)
        } else {
            console.warn('Index not found, could not update an answer:', answer)
        }
    },
    setPreloaded (state, value) {
        Vue.set(state.preloaded, value, value)
    },
    setStates(state, states) {
        state.states = states
    },
    insertAnswer(state, answer) {
        state.items.push(answer)
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}