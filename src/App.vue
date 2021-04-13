<template>
  <div id="pexeso">
    <modal @start="start" :html="html"></modal>
    <pexeso :playersObj="playersArr" :cardsObj="cardsArr" :msg="this.msg"/>
  </div>
</template>

<script>
import Pexeso from './components/Pexeso.vue'
import Modal from './components/Modal.vue'

export default {
  name: 'App',
  components: {
    Pexeso,
    Modal
  },
  data() {
    return {
      playersArr: [],
      cardsArr: [],
      msg: 'Vitajte v hre Pexeso',
    }
  },
  methods: {
    start(params, params2) {
      this.cardsArr = params2;
      this.playersArr = params;
    }
  }
}
</script>

<style>
#pexeso {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
  padding: 30px;
}
</style>
