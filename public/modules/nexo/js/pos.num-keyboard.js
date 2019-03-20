Vue.component( 'num-keyboard', {
    data: () => {

    },
    template: `
    <div class="keyboard-wrapper flex-fill w-100 d-flex flex-column">    
        <div class="input-group input-group-lg p-2 rounded-0">
            <input type="text" class="form-control rounded-0" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-lg">
        </div>
        <hr class="m-0 border-1">
        <div class="btn-group-vertical m-0 flex-fill w-100 d-flex flex-column" role="group">
            <div class="btn-group flex-fill">
                <button type="button" class="btn btn-outline-primary border-bottom-0 border-top-0 rounded-0 border-left-0">
                    <h1 class="m-0">7</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 border-top-0">
                    <h1 class="m-0">8</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 border-top-0 rounded-0">
                    <h1 class="m-0">9</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 border-top-0 rounded-0 border-right-0">
                    <h1 class="m-0">&times;</h1>
                </button>
            </div>
            <div class="btn-group flex-fill">
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0 border-left-0">
                    <h1 class="m-0">4</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0">
                    <h1 class="m-0">5</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0">
                    <h1 class="m-0">6</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0 border-right-0">
                    <h1 class="m-0">+</h1>
                </button>
            </div>
            <div class="btn-group flex-fill">
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0 border-left-0">
                    <h1 class="m-0">1</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0">
                    <h1 class="m-0">2</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0">
                    <h1 class="m-0">3</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0 border-right-0">
                    <h1 class="m-0">-</h1>
                </button>
            </div>
            <div class="btn-group flex-fill">
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0 border-left-0">
                    <h1 class="m-0">±</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0">
                    <h1 class="m-0">0</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0">
                    <h1 class="m-0">.</h1>
                </button>
                <button type="button" class="btn btn-outline-primary border-bottom-0 rounded-0 border-right-0">
                    <h1 class="m-0">=</h1>
                </button>
            </div>
        </div>
    </div>
    `
})