class MinesweeperGame {
    constructor(config) {
        // конфиг
        this.config = {
            difficulty: 'beginner',
            width: 9,
            height: 9,
            mines: 10,
            ...config
        };

        // Состояние игры
        this.board = [];
        this.gameState = 'ready'; // ready, playing, paused, won, lost
        this.startTime = null;
        this.timerInterval = null;
        this.elapsedTime = 0;
        this.movesCount = 0;
        this.flagsCount = 0;
        this.revealedCount = 0;
        this.hintsLeft = 3;
        this.firstClick = true;

        // DOM элементы
        this.elements = {
            board: document.getElementById('game-board'),
            timer: document.getElementById('game-timer'),
            minesCount: document.getElementById('mines-count'),
            movesCount: document.getElementById('moves-count'),
            flagsCount: document.getElementById('flags-count'),
            currentScore: document.getElementById('current-score'),
            gameStatus: document.getElementById('game-status'),
            resetBtn: document.getElementById('reset-btn'),
            hintBtn: document.getElementById('hint-btn'),
            pauseBtn: document.getElementById('pause-btn'),
            newGameBtn: document.getElementById('new-game-btn')
        };

        // Инициализация
        this.init();
        this.setupEventListeners();
    }

    
    init() {
        this.createBoard();
        this.renderBoard();
        this.updateUI();
    }

    // генерация игрового поля
    createBoard() {
        this.board = [];
        for (let y = 0; y < this.config.height; y++) {
            this.board[y] = [];
            for (let x = 0; x < this.config.width; x++) {
                this.board[y][x] = {
                    x, y,
                    isMine: false,
                    isRevealed: false,
                    isFlagged: false,
                    neighborMines: 0,
                    element: null
                };
            }
        }
    }

    //размещаем минки после первого клика
    placeMines(firstX, firstY) {
        let minesPlaced = 0;

        // Гарантируем, что первая клетка не будет миной
        const safeCells = this.getNeighbors(firstX, firstY);
        safeCells.push({x: firstX, y: firstY});

        while (minesPlaced < this.config.mines) {
            const x = Math.floor(Math.random() * this.config.width);
            const y = Math.floor(Math.random() * this.config.height);

            // Пропускаем безопасные клетки
            if (safeCells.some(cell => cell.x === x && cell.y === y)) {
                continue;
            }

            if (!this.board[y][x].isMine) {
                this.board[y][x].isMine = true;
                minesPlaced++;
            }
        }

        // Подсчитываем мины вокруг каждой клетки
        this.calculateNeighborMines();
    }

    //считаем минки вокруг клеточек
    calculateNeighborMines() {
        for (let y = 0; y < this.config.height; y++) {
            for (let x = 0; x < this.config.width; x++) {
                if (!this.board[y][x].isMine) {
                    this.board[y][x].neighborMines = this.countNeighborMines(x, y);
                }
            }
        }
    }

    //считаем мины возле конкретных клеток
    countNeighborMines(x, y) {
        let count = 0;
        const neighbors = this.getNeighbors(x, y);

        neighbors.forEach(cell => {
            if (this.board[cell.y][cell.x].isMine) {
                count++;
            }
        });

        return count;
    }

    //получение соседней клетки
    getNeighbors(x, y) {
        const neighbors = [];
        for (let dy = -1; dy <= 1; dy++) {
            for (let dx = -1; dx <= 1; dx++) {
                if (dx === 0 && dy === 0) continue;

                const nx = x + dx;
                const ny = y + dy;

                if (nx >= 0 && nx < this.config.width && ny >= 0 && ny < this.config.height) {
                    neighbors.push({x: nx, y: ny});
                }
            }
        }
        return neighbors;
    }

    //отрисовка поля
    renderBoard() {
        this.elements.board.innerHTML = '';
        this.elements.board.style.gridTemplateColumns = `repeat(${this.config.width}, 35px)`;

        for (let y = 0; y < this.config.height; y++) {
            for (let x = 0; x < this.config.width; x++) {
                const cell = this.board[y][x];
                const cellElement = document.createElement('div');

                cellElement.className = 'cell';
                cellElement.dataset.x = x;
                cellElement.dataset.y = y;

                // Добавляем классы
                if (cell.isRevealed) {
                    cellElement.classList.add('revealed');
                    if (cell.isMine) {
                        cellElement.classList.add('mine');
                    } else if (cell.neighborMines > 0) {
                        cellElement.classList.add(`num-${cell.neighborMines}`);
                        cellElement.textContent = cell.neighborMines;
                    }
                } else if (cell.isFlagged) {
                    cellElement.classList.add('flagged');
                }

                // Сохраняем ссылку на элемент
                cell.element = cellElement;
                this.elements.board.appendChild(cellElement);
            }
        }
    }

    //обработчик клика по клетке
    handleCellClick(x, y, isRightClick = false) {
        const cell = this.board[y][x];

        // Игра не активна
        if (this.gameState !== 'playing' && this.gameState !== 'ready') {
            return;
        }

        // Первый клик - начинаем игру
        if (this.firstClick && !isRightClick) {
            this.firstClick = false;
            this.gameState = 'playing';
            this.startTimer();
            this.placeMines(x, y);
            this.updateUI();
        }

        // Правая кнопка - флаг
        if (isRightClick && !cell.isRevealed) {
            this.toggleFlag(x, y);
            return;
        }

        // Левая кнопка
        if (!cell.isRevealed && !cell.isFlagged) {
            this.revealCell(x, y);
        }
    }

    //открытие клетки
    revealCell(x, y) {
        const cell = this.board[y][x];

        if (cell.isRevealed || cell.isFlagged) {
            return;
        }

        // Открываем клетку
        cell.isRevealed = true;
        this.revealedCount++;
        this.movesCount++;

        // Если мина игра окончена
        if (cell.isMine) {
            cell.element.classList.add('exploded');
            this.gameOver(false);
            return;
        }

        // Обновляем отображение
        cell.element.classList.add('revealed');
        if (cell.neighborMines > 0) {
            cell.element.classList.add(`num-${cell.neighborMines}`);
            cell.element.textContent = cell.neighborMines;
        } else {
            // Рекурсивно открываем соседей, если нет мин вокруг
            this.revealNeighbors(x, y);
        }

        // Проверяем победу
        if (this.checkWin()) {
            this.gameOver(true);
        }

        this.updateUI();
    }

    //рекурсивно открываем сосед. клетки
    revealNeighbors(x, y) {
        const neighbors = this.getNeighbors(x, y);

        neighbors.forEach(neighbor => {
            const cell = this.board[neighbor.y][neighbor.x];
            if (!cell.isRevealed && !cell.isFlagged && !cell.isMine) {
                this.revealCell(neighbor.x, neighbor.y);
            }
        });
    }

    //переключение флага
    toggleFlag(x, y) {
        const cell = this.board[y][x];

        if (cell.isRevealed) {
            return;
        }

        if (cell.isFlagged) {
            cell.isFlagged = false;
            this.flagsCount--;
            cell.element.classList.remove('flagged');
        } else {
            // Проверяем, не превышен ли лимит флагов
            if (this.flagsCount >= this.config.mines) {
                return;
            }
            cell.isFlagged = true;
            this.flagsCount++;
            cell.element.classList.add('flagged');
        }

        this.updateUI();
    }

    //проверка победы
    checkWin() {
        const totalCells = this.config.width * this.config.height;
        return this.revealedCount === totalCells - this.config.mines;
    }

    //завершение
    gameOver(isWin) {
        this.gameState = isWin ? 'won' : 'lost';
        this.stopTimer();

        // Показываем все мины
        if (!isWin) {
            this.revealAllMines();
        }

        // Обновляем UI
        this.elements.resetBtn.textContent = isWin ? '😎' : '😵';
        this.elements.resetBtn.classList.add(isWin ? 'game-won' : 'game-over');

        if (isWin) {
            this.elements.gameStatus.textContent = 'You Win!';
            this.elements.gameStatus.style.color = '#28a745';
            this.saveGameResult();
        } else {
            this.elements.gameStatus.textContent = 'Game Over';
            this.elements.gameStatus.style.color = '#ff6b6b';
        }

        this.showGameOverModal(isWin);
    }

    //показать все мины
    revealAllMines() {
        for (let y = 0; y < this.config.height; y++) {
            for (let x = 0; x < this.config.width; x++) {
                const cell = this.board[y][x];
                if (cell.isMine && !cell.isRevealed) {
                    cell.isRevealed = true;
                    cell.element.classList.add('revealed', 'mine');
                }
            }
        }
    }

    //сохранение результата
    async saveGameResult() {
        console.log('Saving game result...');
        console.log('Game config:', gameConfig);
        console.log('isLoggedIn:', gameConfig.isLoggedIn);
        console.log('userId:', gameConfig.userId);

        if (!gameConfig.isLoggedIn) {
            console.log('User not logged in, score not saved');
            return;
        }

        const gameData = {
            difficulty: this.config.difficulty,
            time: this.elapsedTime,
            moves: this.movesCount,
            flags: this.flagsCount,
            result: 'won'
        };

        console.log('Game data to save:', gameData);

        try {
            const response = await fetch(gameConfig.apiBaseUrl + 'save_game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ...gameData,
                    userId: gameConfig.userId
                })
            });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            const result = await response.json();
            console.log('Server response:', result);

            if (result.success && result.score) {
                this.elements.currentScore.textContent = result.score;
            }
        } catch (error) {
            console.error('Error saving game:', error);
            console.error('Error details:', error.message);
        }
    }

    //модальное окно в конце игры
    showGameOverModal(isWin) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'flex';

        const timeFormatted = this.formatTime(this.elapsedTime);
        const score = this.calculateScore();

        modal.innerHTML = `
            <div class="modal-content">
                <h2>${isWin ? 'Congratulations!' : 'Game Over'}</h2>
                <p>
                    ${isWin ? 'You cleared the minefield!' : 'You hit a mine!'}<br><br>
                    <strong>Time:</strong> ${timeFormatted}<br>
                    <strong>Moves:</strong> ${this.movesCount}<br>
                    <strong>Flags:</strong> ${this.flagsCount}<br>
                    ${isWin ? `<strong>Score:</strong> ${score}<br>` : ''}
                </p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button id="play-again-btn" class="game-btn">Play Again</button>
                    <button id="close-modal-btn" class="game-btn" style="background: #393E46;">✕ Close</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        //обработчики кнопок
        modal.querySelector('#play-again-btn').addEventListener('click', () => {
            document.body.removeChild(modal);
            this.resetGame();
        });

        modal.querySelector('#close-modal-btn').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
    }

    //считаем очки
    calculateScore() {
        if (this.gameState !== 'won') return 0;

        const baseScore = {
            'beginner': 1000,
            'intermediate': 2500,
            'expert': 5000
        }[this.config.difficulty];

        const timeBonus = Math.max(0, 1000 - this.elapsedTime);
        const efficiencyBonus = Math.max(0, 500 - (this.movesCount * 2));

        return baseScore + timeBonus + efficiencyBonus;
    }

    
    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    
    startTimer() {
        this.startTime = Date.now();
        this.timerInterval = setInterval(() => {
            this.elapsedTime = Math.floor((Date.now() - this.startTime) / 1000);
            this.elements.timer.textContent = this.elapsedTime;
        }, 1000);
    }

    stopTimer() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }
    }

    
    resetGame() {
        this.stopTimer();

        this.gameState = 'ready';
        this.startTime = null;
        this.elapsedTime = 0;
        this.movesCount = 0;
        this.flagsCount = 0;
        this.revealedCount = 0;
        this.hintsLeft = 3;
        this.firstClick = true;

        this.createBoard();
        this.renderBoard();
        this.updateUI();

        this.elements.resetBtn.textContent = '😊';
        this.elements.resetBtn.className = 'game-btn';
        this.elements.resetBtn.id = 'reset-btn';
    }

    
    useHint() {
        if (this.hintsLeft <= 0 || this.gameState !== 'playing') {
            return;
        }

        // Находим безопасную клетку
        let safeCell = null;
        for (let y = 0; y < this.config.height; y++) {
            for (let x = 0; x < this.config.width; x++) {
                const cell = this.board[y][x];
                if (!cell.isRevealed && !cell.isFlagged && !cell.isMine) {
                    safeCell = cell;
                    break;
                }
            }
            if (safeCell) break;
        }

        if (safeCell) {
            this.hintsLeft--;
            safeCell.element.classList.add('hint');

            setTimeout(() => {
                if (safeCell.element) {
                    safeCell.element.classList.remove('hint');
                }
            }, 2000);

            this.updateUI();
        }
    }

    
    togglePause() {
        if (this.gameState === 'paused') {
            this.gameState = 'playing';
            this.startTimer();
            this.elements.pauseBtn.textContent = 'Пауза';
        } else if (this.gameState === 'playing') {
            this.gameState = 'paused';
            this.stopTimer();
            this.elements.pauseBtn.textContent = 'Продолжить';
        }

        this.updateUI();
    }

    //обновление ui
    updateUI() {
        // Таймер
        this.elements.timer.textContent = this.elapsedTime;

        // Счетчики
        this.elements.minesCount.textContent = this.config.mines - this.flagsCount;
        this.elements.movesCount.textContent = this.movesCount;
        this.elements.flagsCount.textContent = this.flagsCount;

        // Очки
        if (this.gameState === 'won') {
            this.elements.currentScore.textContent = this.calculateScore();
        }

        // Статус
        const statusMap = {
            'ready': 'Ready',
            'playing': 'Playing',
            'paused': 'Paused',
            'won': 'You Win!',
            'lost': 'Game Over'
        };

        this.elements.gameStatus.textContent = statusMap[this.gameState] || 'Ready';

        // Подсказки
        this.elements.hintBtn.textContent = `Hint (${this.hintsLeft} left)`;
        this.elements.hintBtn.disabled = this.hintsLeft <= 0 || this.gameState !== 'playing';

        // Кнопка паузы
        this.elements.pauseBtn.disabled = this.gameState === 'ready' || this.gameState === 'won' || this.gameState === 'lost';
    }

    //настройка обработчиков событий
    setupEventListeners() {
        // Клики по клеткам
        this.elements.board.addEventListener('click', (e) => {
            if (!e.target.classList.contains('cell')) return;

            const x = parseInt(e.target.dataset.x);
            const y = parseInt(e.target.dataset.y);
            this.handleCellClick(x, y, false);
        });

        // Правая кнопка мыши (флаги)
        this.elements.board.addEventListener('contextmenu', (e) => {
            e.preventDefault();

            if (!e.target.classList.contains('cell')) return;

            const x = parseInt(e.target.dataset.x);
            const y = parseInt(e.target.dataset.y);
            this.handleCellClick(x, y, true);
        });

        // Кнопка сброса
        this.elements.resetBtn.addEventListener('click', () => {
            this.resetGame();
        });

        // Подсказка
        this.elements.hintBtn.addEventListener('click', () => {
            this.useHint();
        });

        // Пауза
        this.elements.pauseBtn.addEventListener('click', () => {
            this.togglePause();
        });

        // Новая игра
        this.elements.newGameBtn.addEventListener('click', () => {
            this.resetGame();
        });

        // Смена сложности
        document.querySelectorAll('.difficulty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Убираем активный класс у всех
                document.querySelectorAll('.difficulty-btn').forEach(b => {
                    b.classList.remove('active');
                });

                // Добавляем активный класс текущей
                btn.classList.add('active');

                // Меняем сложность
                const difficulty = btn.dataset.difficulty;
                this.changeDifficulty(difficulty);
            });
        });
    }

    
    changeDifficulty(difficulty) {
        const configs = {
            'beginner': { width: 9, height: 9, mines: 10 },
            'intermediate': { width: 16, height: 16, mines: 40 },
            'expert': { width: 30, height: 16, mines: 99 }
        };

        this.config = { difficulty, ...configs[difficulty] };
        this.resetGame();
    }
}

// Инициализация игры при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Создаем экземпляр игры
    const game = new MinesweeperGame({
        difficulty: 'beginner'
    });

    // Делаем глобально доступным для отладки
    window.minesweeperGame = game;

    console.log('инициализация прошла успешно');
});