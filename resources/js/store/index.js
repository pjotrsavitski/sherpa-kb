import Vue from 'vue'
import Vuex from 'vuex'
import app from './modules/app'
import questions from './modules/questions'
import pendingQuestions from './modules/pending_questions'
import answers from './modules/answers'
import topics from './modules/topics'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        app,
        questions,
        pendingQuestions,
        answers,
        topics
    }
})
