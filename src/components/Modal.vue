<template>
  <div @keyup.esc="closeModal" v-show="showModal" id="modalWindow" class="modal-window" tabindex="0">
    <div class="modal-content">
      <h1>Vitajte v hre pexeso!</h1>
      <div class="row">
        <div class="col">
          <h3>Nastavenia hry</h3>
          <div>
            <p class="float-left">Počet hráčov</p>
            <div class="float-right numberOfPlayers">
              <input class="mr-1" checked type="radio" id="onePlayer" @click="secondPlayerMethod(false)" value="1" name="numberOfPlayers">
              <label class="mr-3" for="onePlayer" id="onePlayerLabel"> Jeden </label>
              <input class="mr-1" type="radio" id="twoPlayers" @click="secondPlayerMethod(true)" value="2" name="numberOfPlayers">
              <label for="twoPlayers" id="twoPlayersLabel"> Dvaja </label>
            </div>
          </div>
          <div class="clear"></div>
          <div>
            <p class="float-left">Mená hráčov:</p>
            <div class="float-right">
              <div class="name-input mb-2">
                <input v-model="names[0]" type="text" id="onePlayerName" name="playerNames" placeholder="Meno hráča">
              </div>
              <div class="name-input" v-show="secondPlayer">
                <input v-model="names[1]" type="text" id="secondPlayerName" name="playerNames" placeholder="Meno druhého hráča">
              </div>
            </div>
          </div>
          <div class="clear"></div>
          <div>
            <p class="float-left">Počet kariet</p>
            <div class="float-right numberOfPlayers">
              <input checked class="mr-1" type="radio" id="cardsFirstOption" value="16" name="numberOfCards">
              <label class="mr-3" for="cardsFirstOption"> 16 </label>
              <input class="mr-1" type="radio" id="cardsSecondOption" value="20" name="numberOfCards">
              <label class="mr-3" for="cardsSecondOption"> 20 </label>
              <input class="mr-1" type="radio" id="cardsThirdOption" value="24" name="numberOfCards">
              <label for="cardsThirdOption"> 24 </label>
            </div>
          </div>
        </div>
        <div class="col border-left-custom">
          <div>
          <h3>
            Uložené hry
          </h3>
            <table style="width: 100%" v-if="savedGame">
              <tr>
                <th>Hráč 1</th>
                <th>Hráč 2</th>
                <th>Čas uloženia</th>
                <th></th>
              </tr>
              <tr>
                <td>{{savedGame.player1Name}}</td>
                <td>{{ savedGame.player2Name ? savedGame.player2Name :'Hráč 1 hral sám' }}</td>
                <td>{{savedGame.timeOfSaving}}</td>
                <td><button class="btn btn-sm btn-success" @click="loadGame">Načítať</button></td>
              </tr>
            </table>
            <p v-else>
              Nie sú žiadne uložené hry
            </p>
          </div>
          <div>
            <h3>
              Uložená hra z DB
            </h3>
            <table style="width: 100%" v-if="savedGameOnline">
              <tr>
                <th>Hráč 1</th>
                <th>Hráč 2</th>
                <th>Čas uloženia</th>
                <th></th>
              </tr>
              <tr>
                <td>{{savedGameOnline.player1Name}}</td>
                <td>{{ savedGameOnline.player2Name ? savedGameOnline.player2Name :'Hráč 1 hral sám' }}</td>
                <td>{{savedGameOnline.timeOfSaving}}</td>
                <td><button class="btn btn-sm btn-success" @click="loadGameOnline">Načítať</button></td>
              </tr>
            </table>
            <p v-else>
              Nie sú žiadne uložené hry
            </p>
          </div>
        </div>
      </div>
      <div class="modal-bottom">
        <button class="btn btn-success" @click="play">
          Hrať!
        </button>
      </div>
      <footer>
        <div class="text-center p-2">
          Táto aplikácia vznikla ako súčasť bakalárskej práce <br>
          Autor: <strong>Felix Strelecký</strong> <br>
          Akademický rok: 2020/2021
        </div>
      </footer>
    </div>
  </div>
</template>


<script>
import axios from "axios";
let playerId = 1
class Player {
  constructor(name, score = 0, moves = 1, onTurn = 0, strike = 0, id = playerId) {
    playerId++
    this.id = id;
    this.name = name;
    this.score = score;
    this.moves = moves;
    this.onTurn = onTurn;
    this.strike = strike;
  }
}

let cardId = 1
let colorsFor16 = [
  'red',
  'red',
  'blue',
  'blue',
  'orange',
  'orange',
  'yellow',
  'yellow',
  'green',
  'green',
  'pink',
  'pink',
  'coral',
  'coral',
  'black',
  'black'
]

let colorsFor20 = [
  'red',
  'red',
  'gold',
  'gold',
  'lightblue',
  'lightblue',
  'orange',
  'orange',
  'yellow',
  'yellow',
  'lightgreen',
  'lightgreen',
  'darkgreen',
  'darkgreen',
  'pink',
  'pink',
  'coral',
  'coral',
  'black',
  'black'
]

let colorsFor24 = [
  'red',
  'red',
  'gold',
  'gold',
  'lightblue',
  'lightblue',
  'orange',
  'orange',
  'yellow',
  'yellow',
  'lightgreen',
  'lightgreen',
  'darkgreen',
  'darkgreen',
  'mediumorchid',
  'mediumorchid',
  'pink',
  'pink',
  'coral',
  'coral',
  'black',
  'black',
  'brown',
  'brown'
]

class Card {
  constructor(numberOfCards, background, flipped = 0, finished = 0, id = cardId) {
    cardId++
    this.id = id;
    this.background = this.getBackground(numberOfCards)
    this.flipped = flipped;
    this.finished = finished;
  }
  getBackground(numberOfCards) {
    var color = null;
    switch (numberOfCards) {
      case '16':
        color = colorsFor16.splice(Math.floor(Math.random() * colorsFor16.length), 1);
        break;
      case '20':
        color = colorsFor20.splice(Math.floor(Math.random() * colorsFor20.length), 1);
        break;
      case '24':
        color = colorsFor24.splice(Math.floor(Math.random() * colorsFor24.length), 1);
    }
    return color[0]
  }
}

export default {
  name: 'Modal',
  data() {
    return {
      secondPlayer: false,
      names: {
        0: 'Player 1',
        1: 'Player 2'
      },
      showModal: true,
      playersArr: [],
      cardsArr: [],
      savedGameDataOnline: {}
    }
  },
  methods: {
    closeModal() {
      this.showModal = false;
    },
    play() {
        var numberOfPlayers = document.querySelector('input[name = numberOfPlayers]:checked');
        var numberOfCards = document.querySelector('input[name = numberOfCards]:checked');
        if (numberOfPlayers === null) {
          alert('Zvolte počet hráčov!')
          return;
        }
        if (numberOfCards === null) {
          alert('Zvolte počet kariet!')
          return
        }
        for (var i = 0; i < numberOfPlayers.value; i++) {
          this.playersArr.push(new Player(this.names[i]));
        }
        for (var x = 0; x < numberOfCards.value; x++) {
          this.cardsArr.push(new Card(numberOfCards.value));
        }
        this.playersArr[0].onTurn = 1;
        this.$emit('start', this.playersArr, this.cardsArr);


      this.closeModal();
    },
    secondPlayerMethod(boolean) {
      this.secondPlayer = boolean;
    },
    loadGame() {
      this.$emit('start', this.savedGameData[1], this.savedGameData[0])
      this.closeModal()
      alert('Vitajte späť v hre!')
    },
    loadGameOnline() {
      this.$emit('start', this.savedGameDataOnline[1], this.savedGameDataOnline[0])
      this.closeModal()
      alert('Vitajte späť v hre!')
    }
  },
  computed: {
    savedGame() {
      if (localStorage.savedGame) {
        var savedGameArr = JSON.parse(localStorage.savedGame)
        return savedGameArr[2];
      }
      else return false;
    },
    savedGameData() {
      if (localStorage.savedGame) {
        return JSON.parse(localStorage.savedGame)
      } else return false;
    },
    savedGameOnline() {
      if (this.savedGameDataOnline) {
        return this.savedGameDataOnline[2]
      } else return false;
    }
  },
  created() {
      axios.get('http://localhost/pexeso/public/get.php')
          .then(response => (this.savedGameDataOnline = response.data)).catch(e => console.log(e))
  },
}
</script>

<style>

.clear {
  clear: both;
}

.modal-window {
  position: fixed;
  z-index: 1;
  padding-top: 100px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgb(0,0,0);
  background-color: rgba(0,0,0,0.4);
}
.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px 20px 0 20px;
  border: 1px solid #888;
  width: 80%;
}
.border-left-custom {
  border-left: 5px dotted black;
}
.modal-bottom {
  margin-top: 10px;
  padding: 15px 0;
  border-top: 3px solid black;
}
.name-input {
  border: 1px solid grey;
  border-radius: 0.5rem;
  padding: 0 10px;
}
.name-input input, .name-input input:focus, .name-input input:hover {
  border: none;
  outline: none;
}

</style>