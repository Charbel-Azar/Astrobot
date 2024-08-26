const cards = document.querySelectorAll('.memory-card'); //Select all card elements

var stopwatch = {
    eltime : null, // holds HTML time display
    timer : null, // timer object
    startTime: 0, // start time in milliseconds
    elapsedTime: 0, // elapsed time in microseconds
    init : function () {
      // Get HTML elements
	  stopwatch.etime = document.getElementsByClassName("stopwatch-time")[0]; //Span element on main page
	  stopwatch.etime_end = document.getElementsByClassName("stopwatch-time")[1]; //Span element on win box
	  stopwatch.etime_form = document.getElementsByClassName("stopwatch-time")[2]; //Hidden Input tag 
    },
  
    tick : function () {
    // tick() : update display if stopwatch running
      const now = performance.now(); // High-resolution time in milliseconds
      stopwatch.elapsedTime = now - stopwatch.startTime;

      const totalSeconds = stopwatch.elapsedTime / 1000;
      const hours = Math.floor(totalSeconds / 3600);
      const mins = Math.floor((totalSeconds % 3600) / 60);
      const secs = Math.floor(totalSeconds % 60);
      const milliseconds = Math.floor(stopwatch.elapsedTime % 1000);
  
      // Update the display timer
      stopwatch.etime.innerHTML = 
          `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}.${milliseconds.toString().padStart(3, '0')}`;
      stopwatch.etime_end.innerHTML = 
          `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}.${milliseconds.toString().padStart(3, '0')}`;
      stopwatch.etime_form.value = stopwatch.elapsedTime.toFixed(3); // Store time in milliseconds with 3 decimals for microseconds
    },
  
    start : function () {
    // start the stopwatch
      stopwatch.startTime = performance.now(); // Set the start time to the current time
      stopwatch.timer = setInterval(stopwatch.tick, 10); // Update every 10 milliseconds for better precision
    },
  
    stop  : function () {
    // stop the stopwatch
      clearInterval(stopwatch.timer);
      stopwatch.timer = null;
      stopwatch.tick(); // Update one last time after stopping
    },
  
    reset : function () {
    // reset the stopwatch
      // Stop if running
      if (stopwatch.timer != null) { stopwatch.stop(); }
  
      // Reset time
      stopwatch.elapsedTime = 0;
      stopwatch.tick();
    }
};

let gameHasStarted = false; // Variable that checks if game has started
let hasFlippedCard = false; // Variable that checks if a card has been flipped
let lockBoard = false; // Variable that blocks clicks on cards while cards are not flipped back
let firstCard, secondCard; 
let Matches = 0; // Variable that checks the total number of matches
let winbox = document.getElementsByClassName("win-box")[0]; // Get Win-box element
let wincontent = document.getElementsByClassName("win-content")[0]; // Get win-content element

function flipCard() {
	if(!gameHasStarted) {
        // Starts the stopwatch if first flip is done and game has started
        stopwatch.start();
    }
    gameHasStarted = true;
	if (lockBoard) return; // If lockBoard is set to true player cannot flip other cards
	if (this === firstCard) return; // If the player clicks on the first card he has already flipped, then return
	this.classList.add('flip'); // Add class flip to element

	if (!hasFlippedCard) {
        // Checks if first card has not been flipped
        hasFlippedCard = true;
        firstCard = this;
        return;
    }

	secondCard = this;
	checkForMatch();
}

function checkForMatch() {
    // Matches cards by comparing data attribute of both cards
	let isMatch = firstCard.dataset.framework === secondCard.dataset.framework;
	isMatch ? disableCards() : unflipCards(); // Check if isMatch is true or false and execute either disableCards or unflipCards function
}

function disableCards() {
    // Remove click event from cards that are disabled
	firstCard.removeEventListener('click', flipCard);
	secondCard.removeEventListener('click', flipCard);
	Matches += 1; // Increase matches amount
	
	resetBoard();
}

function unflipCards() {
    lockBoard = true;

    setTimeout(() => {
        // Flip the cards back by removing flip class
        firstCard.classList.remove('flip'); 
        secondCard.classList.remove('flip');
        resetBoard();
    }, 750);
}

function resetBoard() {
   [hasFlippedCard, lockBoard] = [false, false]; // Set these variables to be false
   [firstCard, secondCard] = [null, null]; // Set these variables to be null
   if (Matches == cardNumber / 2) { // Check if all cards have been flipped
       winbox.classList.add('on');
       wincontent.classList.add('on');
       stopwatch.stop(); // Stop the stopwatch
   }
}

(function shuffle() {
   cards.forEach(card => {
     let randomPos = Math.floor(Math.random() * cardNumber);
     card.style.order = randomPos;
   });
 })();

cards.forEach(card => card.addEventListener('click', flipCard)); // Check for card click and execute flipCard function if clicked
window.addEventListener("load", stopwatch.init); // Initialize stopwatch functions after the page is loaded
function checkWinCondition() {
  const allCards = document.querySelectorAll('.memory-card');
  const allFlipped = Array.from(allCards).every(card => card.classList.contains('flip'));

  if (allFlipped) {
      const finalTime = document.querySelector('.stopwatch-time').textContent;
      endGame(finalTime);
  }
}