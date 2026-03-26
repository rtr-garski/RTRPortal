<?php include 'partials/app-html.php'; ?>

<head>
    <?php 
        $pageTitle = 'Application Intelligence';
        $pageName = 'RealTimeRecords';
        include 'partials/app-meta-title.php'; 
        include 'partials/app-head-css.php'; ?>
</head>

<body>

    <div class="app-wrap">

        <?php include 'partials/app-header.php'; ?>

        <?php include 'partials/app-sidebar.php'; ?>

        <main class="app-body">
            <div class="app-content">

                <div class="content-wrapper">

                    <?php
                        $pageTitle = "Test Index";
                        $pageSubTitle1 = "Config";
                        $pageSubTitle2 = "Intel";
                        $pageSubText = "Test Subtext styled and customizable input elements using classes like <code>.form-control</code>,<code>.form-check</code>";

                        include 'partials/app-pagetitle.php';
                    ?>
                   

                    <div class="main-content">

                        <div class="row">
                            <div class="col-xl-6">
                                <div id="panel-1" class="panel panel-icon">
                                    <div class="panel-hdr">
                                        <h2>
                                            General <span class="fw-300"><i>inputs</i></span>
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                <svg class="sa-icon">
                                                    <use class="panel-collapsed-icon" href="img/sprite.svg#minus-circle"></use>
                                                    <use class="panel-expand-icon" href="img/sprite.svg#plus-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#stop-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#x-circle"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-container show">
                                        <div class="panel-content">
                                            <div class="panel-tag">
                                                <p>Be sure to use an appropriate type attribute on all inputs (e.g., code <code>email</code> for email address or <code>number</code> for numerical information) to take advantage of newer input controls like email verification, number selection, and more.</p>
                                            </div>
                                            <form>
                                                <div class="mb-3">
                                                    <label class="form-label" for="simpleinput">Text</label>
                                                    <input type="text" id="simpleinput" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-email-2">Email</label>
                                                    <input type="email" id="example-email-2" name="example-email-2" class="form-control" placeholder="Email">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-password">Password</label>
                                                    <input type="password" id="example-password" class="form-control" value="password">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-palaceholder">Placeholder</label>
                                                    <input type="text" id="example-palaceholder" class="form-control" placeholder="placeholder">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-textarea">Text area</label>
                                                    <textarea class="form-control" id="example-textarea" rows="5"></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-static">Static control</label>
                                                    <input type="text" readonly class="form-control-plaintext" id="example-static" value="email@example.com">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-helping">Helping text</label>
                                                    <input type="text" id="example-helping" class="form-control" placeholder="Helping text">
                                                    <div class="form-text">
                                                        A block of help text that breaks onto a new line and may extend beyond one line.
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-select">Input Select</label>
                                                    <select class="form-select" id="example-select">
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-multiselect">Multiple Select</label>
                                                    <select id="example-multiselect" multiple class="form-select">
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-fileinput">Default file input</label>
                                                    <input type="file" id="example-fileinput" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-datetime-local-input">Date and time</label>
                                                    <input class="form-control" type="datetime-local" value="2023-07-23T11:25:00" id="example-datetime-local-input">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-date">Date</label>
                                                    <input class="form-control" id="example-date" type="date" name="date" value="2023-07-23">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-month">Month</label>
                                                    <input class="form-control" id="example-month" type="month" name="month">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-week">Week</label>
                                                    <input class="form-control" id="example-week" type="week" name="week">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-time-2">Time</label>
                                                    <input class="form-control" id="example-time-2" type="time" name="time">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-time-3">Time</label>
                                                    <input class="form-control" id="example-time-3" type="time" name="time">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-number">Number</label>
                                                    <input class="form-control" id="example-number" type="number" name="number" value="839473">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-color">Color</label>
                                                    <input class="form-control form-control-color" id="example-color" type="color" name="color" value="#727cf5">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-range">Range (form-control)</label>
                                                    <input class="form-control" id="example-range" type="range" name="range" min="0" max="100">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="customRange2">Range</label>
                                                    <input type="range" class="form-range" id="customRange2">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Select (custom)</label>
                                                    <select class="form-select">
                                                        <option selected>Open this select menu</option>
                                                        <option value="1">One</option>
                                                        <option value="2">Two</option>
                                                        <option value="3">Three</option>
                                                    </select>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label">File Browser</label>
                                                    <input type="file" class="form-control" id="customFile">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div id="panel-2" class="panel panel-icon">
                                    <div class="panel-hdr">
                                        <h2>
                                            General <span class="fw-300"><i>inputs (disabled)</i></span>
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                <svg class="sa-icon">
                                                    <use class="panel-collapsed-icon" href="img/sprite.svg#minus-circle"></use>
                                                    <use class="panel-expand-icon" href="img/sprite.svg#plus-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#stop-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#x-circle"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-container show">
                                        <div class="panel-content">
                                            <div class="panel-tag">
                                                <p>
                                                    Add disabled to an <code>&lt;input&gt;</code>, <code>&lt;select&gt;</code>, or <code>&lt;button&gt;</code> to block interactions, with Bootstrap styling them
                                                    accordingly. Controls inside a <code>&lt;fieldset disabled&gt;</code> are also disabled. For <code>&lt;a&gt;</code> elements, Bootstrap
                                                    uses <code>pointer-events: none;</code> and reduced opacity instead.
                                                </p>
                                            </div>
                                            <form>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="simpleinput-disabled">Text disabled</label>
                                                    <input type="text" id="simpleinput-disabled" class="form-control" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-email-disabled">Email disabled</label>
                                                    <input type="email" id="example-email-disabled" name="example-email-disabled" class="form-control" placeholder="Email" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-password-disabled">Password disabled</label>
                                                    <input type="password" id="example-password-disabled" class="form-control" value="password" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-palaceholder-disabled">Placeholder disabled</label>
                                                    <input type="text" id="example-palaceholder-disabled" class="form-control" placeholder="placeholder" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-textarea-disabled">Text area disabled</label>
                                                    <textarea class="form-control" id="example-textarea-disabled" rows="5" disabled></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-static-disabled">Static control disabled</label>
                                                    <input type="text" readonly class="form-control-plaintext" id="example-static-disabled" value="email@example.com" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-helping-disabled">Helping text disabled</label>
                                                    <input type="text" id="example-helping-disabled" class="form-control" placeholder="Helping text" disabled>
                                                    <div class="form-text">
                                                        A block of help text that breaks onto a new line and may extend beyond one line.
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-select-disabled">Input Select disabled</label>
                                                    <select class="form-select" id="example-select-disabled" disabled>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-multiselect-disabled">Multiple Select disabled</label>
                                                    <select id="example-multiselect-disabled" multiple class="form-select" disabled>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-fileinput-disabled">Default file input disabled</label>
                                                    <input type="file" id="example-fileinput-disabled" class="form-control" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-datetime-local-input-disabled">Date and time disabled</label>
                                                    <input class="form-control" type="datetime-local" value="2023-07-23T11:25:00" id="example-datetime-local-input-disabled" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-date-disabled">Date disabled</label>
                                                    <input class="form-control" id="example-date-disabled" type="date" name="date" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-month-disabled">Month disabled</label>
                                                    <input class="form-control" id="example-month-disabled" type="month" name="month" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-week-disabled">Week disabled</label>
                                                    <input class="form-control" id="example-week-disabled" type="week" name="week" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-time-disabled">Time disabled</label>
                                                    <input class="form-control" id="example-time-disabled" type="time" name="time" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-time-disabled-2">Time disabled</label>
                                                    <input class="form-control" id="example-time-disabled-2" type="time" name="time" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-number">Number disabled</label>
                                                    <input class="form-control" id="example-number-disabled" type="number" name="number" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-color">Color disabled</label>
                                                    <input class="form-control form-control-color disabled" id="example-color-disabled" type="color" name="color" value="#ed1c24" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted" for="example-range-disabled">Range disabled</label>
                                                    <input class="form-control" id="example-range-disabled" type="range" name="range" min="0" max="100" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="customRange1" class="form-label text-muted">Range (disabled)</label>
                                                    <input type="range" class="form-range" id="customRange1" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Select (disabled)</label>
                                                    <select class="form-select" disabled>
                                                        <option selected>Open this select menu</option>
                                                        <option value="1">One</option>
                                                        <option value="2">Two</option>
                                                        <option value="3">Three</option>
                                                    </select>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label text-muted">File Browser (disabled)</label>
                                                    <input type="file" class="form-control" id="customFile-2" disabled>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div id="panel-5" class="panel panel-icon">
                                    <div class="panel-hdr">
                                        <h2>
                                            Floating <span class="fw-300"><i>Labels</i></span>
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                <svg class="sa-icon">
                                                    <use class="panel-collapsed-icon" href="img/sprite.svg#minus-circle"></use>
                                                    <use class="panel-expand-icon" href="img/sprite.svg#plus-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#stop-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#x-circle"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-container show">
                                        <div class="panel-content">
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <div class="frame-wrap mb-4">
                                                        <div class="form-floating mb-3">
                                                            <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                                                            <label for="floatingInput">Email address</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                                                            <label for="floatingPassword">Password</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="height: 100px"></textarea>
                                                            <label for="floatingTextarea">Comments</label>
                                                        </div>
                                                        <div class="form-floating">
                                                            <select class="form-select" id="floatingSelect" aria-label="Floating label select example">
                                                                <option selected>Open this select menu</option>
                                                                <option value="1">One</option>
                                                                <option value="2">Two</option>
                                                                <option value="3">Three</option>
                                                            </select>
                                                            <label for="floatingSelect">Works with selects</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div id="panel-6" class="panel panel-icon">
                                    <div class="panel-hdr">
                                        <h2>
                                            Floating <span class="fw-300"><i>Labels (Disabled)</i></span>
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                <svg class="sa-icon">
                                                    <use class="panel-collapsed-icon" href="img/sprite.svg#minus-circle"></use>
                                                    <use class="panel-expand-icon" href="img/sprite.svg#plus-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#stop-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#x-circle"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-container show">
                                        <div class="panel-content">
                                            <div class="row">
                                                <div class="col-xl-12">
                                                    <div class="frame-wrap mb-4">
                                                        <div class="form-floating mb-3">
                                                            <input type="email" class="form-control" id="floatingInputDisabled" placeholder="name@example.com" disabled>
                                                            <label for="floatingInputDisabled">Email address</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="password" class="form-control" id="floatingPasswordDisabled" placeholder="Password" disabled>
                                                            <label for="floatingPasswordDisabled">Password</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextareaDisabled" style="height: 100px" disabled></textarea>
                                                            <label for="floatingTextareaDisabled">Comments</label>
                                                        </div>
                                                        <div class="form-floating">
                                                            <select class="form-select" id="floatingSelectDisabled" aria-label="Floating label select example" disabled>
                                                                <option selected>Open this select menu</option>
                                                                <option value="1">One</option>
                                                                <option value="2">Two</option>
                                                                <option value="3">Three</option>
                                                            </select>
                                                            <label for="floatingSelectDisabled">Works with selects</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div id="panel-3" class="panel panel-icon">
                                    <div class="panel-hdr">
                                        <h2>
                                            Input <span class="fw-300"><i>Sizes</i></span>
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                <svg class="sa-icon">
                                                    <use class="panel-collapsed-icon" href="img/sprite.svg#minus-circle"></use>
                                                    <use class="panel-expand-icon" href="img/sprite.svg#plus-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#stop-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#x-circle"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-container show">
                                        <div class="panel-content">
                                            <div class="panel-tag">
                                                Set heights using classes like <code>.form-control-lg</code>, <code>.form-control-sm</code> and for custom inputs, you can use <code>.form-select-sm</code>, <code>.form-select-lg</code>, and set widths using grid column classes like <code>.col-lg-*</code>
                                            </div>
                                            <form>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-input-small">Small</label>
                                                    <input type="text" id="example-input-small" name="example-input-small" class="form-control form-control-sm" placeholder=".form-control-sm">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-input-normal">Normal</label>
                                                    <input type="text" id="example-input-normal" name="example-input-normal" class="form-control" placeholder="Normal">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="example-input-large">Large</label>
                                                    <input type="text" id="example-input-large" name="example-input-large" class="form-control form-control-lg" placeholder=".form-control-lg">
                                                </div>
                                                <div class="mb-0">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <label for="example-gridsize" class="form-label">Grid Sizes</label>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <a href="responsivegrid.html" title="Responsive Grid" target="_blank">Check out how it works</a>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <input type="text" id="example-gridsize" class="form-control border-primary" placeholder=".col-3">
                                                        </div>
                                                        <div class="col">
                                                            <input type="text" id="example-gridsize-2" class="form-control border-success" placeholder=".col">
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="mt-3">
                                                                <input class="form-range" id="gridrange" type="range" name="gridrange" min="1" max="12" value="3">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div id="panel-4" class="panel panel-icon">
                                    <div class="panel-hdr">
                                        <h2>
                                            Altering <span class="fw-300"><i>with utilities</i></span>
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button type="button" class="btn btn-panel" data-action="panel-collapse" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Toggle">
                                                <svg class="sa-icon">
                                                    <use class="panel-collapsed-icon" href="img/sprite.svg#minus-circle"></use>
                                                    <use class="panel-expand-icon" href="img/sprite.svg#plus-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-fullscreen" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Fullscreen">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#stop-circle"></use>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-panel" data-action="panel-close" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-offset="0,10" data-bs-original-title="Close">
                                                <svg class="sa-icon">
                                                    <use href="icons/sprite.svg#x-circle"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="panel-container show">
                                        <div class="panel-content">
                                            <div class="panel-tag">
                                                You can easily alter the border, border-radius and padding using the utility classes.
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="example-input-square">Square edges</label>
                                                        <input type="text" id="example-input-square" class="form-control rounded-0" placeholder="Square borders">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="example-input-pill">Rounded pill</label>
                                                        <input type="text" id="example-input-pill" class="form-control rounded-pill" placeholder="Rounded pill">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="example-input-material">Material</label>
                                                        <input type="text" id="example-input-material" class="form-control rounded-0 border-top-0 border-start-0 border-end-0 px-0 bg-transparent" placeholder="Material">
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="example-input-border">Border color</label>
                                                        <input type="text" id="example-input-border" class="form-control border-info" placeholder="Border colors">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <?php include 'partials/app-footer.php'; ?>

        </main>

        <?php include 'partials/app-drawer.php'; ?>

        <?php include 'partials/app-settings.php'; ?>

    </div>

    <?php include 'partials/app-scripts.php'; ?>

</body>

</html>