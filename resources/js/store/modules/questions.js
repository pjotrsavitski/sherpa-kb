const state = () => ({
    items: [],
    states: [],
    preloaded: []
})

const getters = {
    totalCount: state => {
        return state.items.length
    },
    forReview: state => {
        return state.items.filter(item => item.status.value !== 'in_translation')
    }
}

const actions = {
    loadAllQuestions({ commit }) {
        return axios.get('/questions')
        .then(response => {
            commit('setQuestions', response.data.data)
        })
        .catch(error => {
            console.error('Questions loading:', error)
        })
    },
    preloadAllQuestions({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('items')) {
            commit('setPreloaded', 'items')
            dispatch('loadAllQuestions')
        }
    },
    loadStates({ commit } ) {
        return axios.get('/questions/states')
        .then(response => {
            commit('setStates', response.data)
        })
        .catch(error => {
            console.error('Question states loading:', error)
        })
    },
    preloadStates({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('states')) {
            commit('setPreloaded', 'states')
            dispatch('loadStates')
        }
    },
    updateQuestion({ commit }, question) {
        commit('updateQuestion', question)
    },
    insertQuestion({ commit }, question) {
        commit('insertQuestion', question)
    },
    deleteQuestion({ commit }, question) {
        return axios.delete(`/questions/${question.id}`)
            .then(response => {
                this.dispatch('questions/localDeleteQuestion', response.data)
            })
    },
    localDeleteQuestion({ commit }, question) {
        commit('deleteQuestion', question)
    },
    answerDeleted({ state }, answer) {
        state.items.forEach(question => {
            if (question.answer && question.answer === answer.id) {
                question.answer = null
            }
        })
    },
    topicDeleted({ state }, topic) {
        state.items.forEach(question => {
            if (question.topic && question.topic.id === topic.id) {
                question.topic = null
            }
        })
    },
    topicUpdated({ state }, topic) {
        state.items.forEach(question => {
            if (question.topic && question.topic.id === topic.id) {
                question.topic.description = topic.description
            }
        })
    }
}

const mutations = {
    setQuestions (state, questions) {
        state.items = questions
    },
    updateQuestion (state, question) {
        const index = state.items.findIndex(item => {
            return item.id === question.id
        })

        if (index !== -1) {
            Vue.set(state.items, index, question)
        }
    },
    setPreloaded (state, value) {
        Vue.set(state.preloaded, value, value)
    },
    setStates(state, states) {
        state.states = states
    },
    insertQuestion(state, question) {
        state.items.push(question)
    },
    deleteQuestion(state, question) {
        const index = state.items.findIndex(item => {
            return item.id === question.id
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
