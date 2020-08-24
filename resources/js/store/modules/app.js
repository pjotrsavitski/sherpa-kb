const state = () => ({
    user: null,
    languages: []
})

const getters = {
    isAuthenticated: state => {
        return !!state.user
    },
    user: state => {
        return state.user
    },
    languages: state => {
        return state.languages
    },
    totalLanguages: state => {
        return state.languages.length
    }
}

const actions = {
    setUser({ commit }, user) {
        commit('setUser', user)
    },
    setLanguages({ commit }, languages) {
        commit('setLanguages', languages)
    }
}

const mutations = {
    setUser(state, user) {
        state.user = user
    },
    setLanguages(state, languages) {
        state.languages = languages
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}