<template>
  <div class="pexeso-full">
    <h1 v-if="!gameFinished">{{ msg }}</h1>
    <div v-if="gameFinished && !draw">
      <p>Víťazom sa stal hráč: <b>{{winner.name}}</b></p>
      <p>S dosiahnutým skóre: <b>{{winnerScore}}</b></p>
      <p>Na víťazstvo potreboval: <b>{{winner.moves}}</b> ťahov</p>
      <button class="btn btn-info" onclick="location.reload(); return false;">Hrať znova</button>
    </div>
    <div v-if="gameFinished && draw">
      <p>Obaja hráči nahrali zhodný počet bodov: <b>{{winnerScore}}</b></p>
      <p>Nezvíťazil ani jeden hráč, hra skončila <b>remízou</b></p>
      <button class="btn btn-info" onclick="location.reload(); return false;">Hrať znova</button>
    </div>
    <div class="heading-panel">
      <div class="row">
        <div class="col">
          <h4>
            Na ťahu je:
          </h4>
          <span>{{this.getPlayerOnTurn.name}}</span>
        </div>
        <div class="col">
          <div>
            <h4>Player's {{getPlayerOnTurn.id}} Score:</h4>
            <span>{{getPlayerOnTurn.score}}</span>
          </div>
        </div>
        <div class="col">
          <div :class="{big: getPlayerOnTurn.strike >= 2, bigger: getPlayerOnTurn.strike >= 4, animate__animated: active, animate__wobble: active}">
            <h4>Player's {{getPlayerOnTurn.id}} strike:</h4>
            <span>{{getPlayerOnTurn.strike + 1}}X</span>
          </div>
        </div>
        <div class="col">
          <h4>Hráčov ťah:</h4>
          <span>{{getPlayerOnTurn.moves}}</span>
        </div>
        <div class="col">
          <h4>Časomiera:</h4>
          <span v-if="timer.started">{{
                (timer.hours > 9 ? timer.hours : "0" + timer.hours)
              + ":"
              + (timer.minutes > 9 ? timer.minutes : "0" + timer.minutes)
              + ":"
              + (timer.seconds > 9 ? timer.seconds : "0" + timer.seconds)
            }}</span>
          <span v-else>Otočte prvú kartu</span>
        </div>
        <div class="col">
          <h4>Akcia</h4>
          <div class="row">
            <div class="col">
              <button class="btn btn-sm btn-success" @click="saveGame">Uložiť hru</button>
            </div>
            <div class="col">
              <button class="btn btn-sm btn-info" onclick="location.reload(); return false;">Nastavenia hry</button>
            </div>
            <div class="col">
              <button class="btn btn-sm btn-warrning" @click="saveGameOnline">Nastavenia hry</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div v-for="card in cards" :key="card.id" class="col-sm-3 d-flex justify-content-center">
        <div v-show="!card.finished"
             :style="{background: card.flipped ? card.background : 'grey'}"
             class="card"
             @click="flip(card, getPlayerOnTurn, timer.started)"
        >
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: 'Pexeso',
  props: {
    msg: String,
    playersObj: {},
    cardsObj: {},
  },
  data() {
    return {
      gameFinished: false,
      draw: false,
      active: false,
      winner: '',
      winnerScore: '',
      timer: {
        started: false,
        seconds: 0,
        minutes: 0,
        hours: 0,
      },
    }
  },
  methods: {
    flip(card, player, startedTimer) {
      if (!startedTimer) {
        this.startTimer();
      }
      if (card.finished) {
        return
      }

      card.flipped = true;

      setTimeout(() => {
        if (this.flippedCards.length === 2) {
          this.checkForIt(player)
        }
      }, 500)

    },
    checkForIt(player) {
      if (this.flippedCards[0].background === this.flippedCards[1].background) {
        alert('Úspech')
        this.flippedCards.forEach(card => {
          card.flipped = 0;
          card.finished = 1;
        });
        player.strike += 1;
        player.score += player.strike*10;
        this.checkForGameFinish();
      } else {
        player.strike = 0;
        alert('Neúspech')
        this.flippedCards.forEach(card => {
              card.flipped = 0;
            }
        )
        this.nextPlayerOnTurn(player)
      }
    },
    checkForGameFinish() {
      if (this.notFinishedCards.length === 0) {
        this.timer.started = false;
        this.gameFinished = true;
        this.winnerScore = Math.max.apply(Math, this.players.map(function(player) { return player.score; }))
        var winners = this.players.filter(player => player.score === this.winnerScore);
        if (winners.length === 1) {
          this.draw = false;
          this.winner = winners[0]
        } else {
          this.draw = true
        }
      }
    },
    nextPlayerOnTurn(player) {
      var nextPlayer = this.players.filter(nextPlayer => nextPlayer.id != player.id)[0];
      player.moves += 1;
      if (nextPlayer) {
        player.onTurn = 0;
        nextPlayer.onTurn = 1;
      }
      else return;
    },
    startTimer() {
      this.timer.started = true;
      setInterval(() => {
          this.timer.seconds += 1;
          if (this.timer.seconds > 59) {
            this.timer.seconds = 0;
            this.timer.minutes += 1;
          }
          if (this.timer.minutes > 59) {
            this.timer.minutes = 0;
            this.timer.hours += 1;
          }
        }, 1000)
    },

    saveGameOnline() {
      var dateTime = this.getTimeForSavingGame();
      var savedGameArr = this.prepareDataForSave();
      savedGameArr.push({
        player1Name: this.players[0].name,
        player2Name: this.players[1] ? this.players[1].name : '',
        timeOfSaving: dateTime
      });
      // var stringified = JSON.stringify(savedGameArr)

      var data = new FormData();

      data.append('savedGameArr', JSON.stringify(savedGameArr));

      axios
          .post('http://localhost/pexeso/public/save.php', data)
          .then(response => {
            console.log(response);
          });
    },
    saveGame() {
      var dateTime = this.getTimeForSavingGame();
      var savedGameArr = this.prepareDataForSave();
      savedGameArr.push({
        player1Name: this.players[0].name,
        player2Name: this.players[1] ? this.players[1].name : '',
        timeOfSaving: dateTime
      });
      var stringified = JSON.stringify(savedGameArr)
      localStorage.setItem('savedGame', stringified);
    },
    getTimeForSavingGame() {
      var today = new Date();
      var date = today.getDate()+'.'+(today.getMonth()+1)+'.'+today.getFullYear();
      var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
      return date+' '+time;
    },
    prepareDataForSave() {
      var savedGameArr = []
      savedGameArr.push(this.cards)
      savedGameArr.push(this.players)
      return savedGameArr
    }
  },
  computed: {
    players() {
      return this.playersObj
    },
    getPlayerOnTurn() {
      return this.players.filter(player => player.onTurn)[0]
    },
    cards() {
      return this.cardsObj
    },
    flippedCards() {
      return this.cards.filter(card => card.flipped)
    },
    notFinishedCards() {
      return this.cards.filter(card => !card.finished)
    }
  },
  watch: {
    'getPlayerOnTurn.strike': function (strike, oldStrike) {
      if (oldStrike > strike) {
        this.active = true;
        setTimeout(() => {
          this.active = false
        }, 2000)
      }
    },
  },
  mounted() {

  }
}
</script>

<style scoped>

.big {
  font-weight: bold;
}
.bigger {
  color: red;
  font-weight: bolder;
}

.shakeIt {
  transform: scaleY(20);
}

.card {
  width: 100px;
  height: 100px;
  margin: 20px;
  transition: background-color 0.3s ease-in-out;
}

.heading-panel {
  padding: 20px 0;
  margin-bottom: 10px;
  border-top: 1px solid grey;
  border-bottom: 1px solid grey;

}

</style>
