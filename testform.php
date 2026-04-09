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
                        </div>
                    </div>

                </div>

            </div>

            <?php include 'partials/app-footer.php'; ?>

        </main>

        <?php include 'partials/app-drawer.php'; ?>

        <?php include 'partials/app-settings.php'; ?>

    </div>

    @@include('./partials/app-scripts.html')

</body>

</html>