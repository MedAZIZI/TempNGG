document.addEventListener('DOMContentLoaded', () => {
    const numberOfPlayersInput = document.getElementById('game_numberOfPlayers');
    const playerNamesContainer = document.getElementById('player-names-container');
    const updatePlayerFields = () => {
        const numberOfPlayers = parseInt(numberOfPlayersInput.value, 10) || 2;
        playerNamesContainer.innerHTML = '';
        for (let i = 0; i < numberOfPlayers; i++) {
            const inputDiv = document.createElement('div');
            inputDiv.classList.add('mb-3');

            const input = document.createElement('input');
            input.type = 'text';
            input.classList.add('form-control');
            input.name = `game[playerNames][${i}]`;
            input.placeholder = `Enter name`;
            input.required = true;

            const label = document.createElement('label');
            label.classList.add('form-label');
            label.textContent = `Player ${i + 1} name`;

            inputDiv.appendChild(label);
            inputDiv.appendChild(input);

            playerNamesContainer.appendChild(inputDiv);
        }
    };
    numberOfPlayersInput.value = 2;
    updatePlayerFields();
    numberOfPlayersInput.addEventListener('input', () => {
        const numberOfPlayers = parseInt(numberOfPlayersInput.value, 10);
        if (numberOfPlayers >= 2 && numberOfPlayers <= 10) {
            updatePlayerFields();
        }
    });
});
