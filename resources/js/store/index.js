import Vue from 'vue';
import Vuex from 'vuex';
import questions from './modules/questions';
import pendingQuestions from './modules/pending_questions';
import answers from './modules/answers';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        questions,
        pendingQuestions,
        answers
    }
})