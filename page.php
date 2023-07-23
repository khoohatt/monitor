<!DOCTYPE html>
    <head>
        <title>Мониторинг процессов</title>
        <style type="style/css"></style>
        <link rel="stylesheet" href="css/style.css" type="text/css" media="screen, print">
    </head>
    <body>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        
        <div id='ready' class='no-print'><div class='message'></div><button class='alertButton'>x</div>

        <table id='mergeTable'>
            <td>
                <h1 id='h1'>Мониторинг процессов</h1>
                <div id='dtpDiv' class='no-print'>
                    <input id='dp' type="datetime-local" class="dtpicker">
                    <label for='dp' id='dtpButton'>История</label>
                </div>
            </td>
            <td>
                <div id='tableBorders'>
                    <table id='dataTable'>
                    </table>
                </div>
            </td>
        </table>

        <div id='procsBorders'>
            <table id='procsTable'>
                <thead id='thead'>
                    <tr>
                        <th><input style='visibility: hidden;' class='searchInput' column='0' type="text" placeholder="" value="">
                            <div onclick='sortTable(0, 0)' class='nameDiv'>Имя процесса</div> <input onclick='showSearch(this)' class='searchButton no-print' type="image" src="images/searchTool.png"/></th>
                        <th><input style='visibility: hidden;' class='searchInput' column='1' type="text" placeholder="">
                            <div onclick='sortTable(1, 0)' class='nameDiv'>Время запуска процесса</div> <input onclick='showSearch(this)' class='searchButton no-print' type="image" src="images/searchTool.png"/></th>
                        <th><input style='visibility: hidden;' class='searchInput' column='2' type="text" placeholder="">
                            <div onclick='sortTable(2, 0)' class='nameDiv'>Объем используемой памяти</div> <input onclick='showSearch(this)' class='searchButton no-print' type="image" src="images/searchTool.png"/></th>
                        <th><input style='visibility: hidden;' class='searchInput' column='3' type="text" placeholder="">
                            <div onclick='sortTable(3, 0)' class='nameDiv'>Количество потоков</div> <input onclick='showSearch(this)' class='searchButton no-print' type="image" src="images/searchTool.png"/></th>
                        <th><input style='visibility: hidden;' class='searchInput' column='4' type="text" placeholder="">
                            <div onclick='sortTable(4, 0)' class='nameDiv'>Идентификатор процесса</div> <input onclick='showSearch(this)' class='searchButton no-print' type="image" src="images/searchTool.png"/></th>
                    </tr>
                </thead>
                <tbody sort='-1' dir='asc' search='' searchCol='-1' id='data'></tbody>
            </table>
            <table id="header_fixed"></table>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
        <script type="text/javascript">
            
        let interval;
        setRefreshing(true);

        function setRefreshing(refresh) {
            if (refresh) {
                interval = setInterval(changeData, 5000);
            } else {
                clearInterval(interval);
            }
            changeData();
        }

        function addTooltip() {
            const elements = [...document.querySelectorAll('[tip]')]

            for (const el of elements) {
                const tip = document.createElement('div');
                tip.classList.add('tooltip');
                tip.textContent = el.getAttribute('tip');
                el.appendChild(tip);
                el.onpointermove = e => {
                    if (e.target !== e.currentTarget) { 
                        return;
                    }
                }
            }
        }

        $(".searchInput").keyup(function(event) {
            if (event.keyCode === 13) {
                searchData(true, this.getAttribute('column'));
            }
        });

        $('.searchInput').blur(function(event) {
            showSearch(event.target);
        });

        $('#clear').on('click', function () {
            document.getElementById('input').value = '';
            changeData();
            document.getElementById('data').setAttribute('search', '');
        });

        function clearSearch() {
            changeData();
            document.getElementById('data').setAttribute('search', ''); 
            document.getElementById('data').setAttribute('searchCol', -1); 
        }

        function sortEls(Element) {
            $.ajax({
                type: 'POST', 
                url: 'changeData.php',  
                data: formData, 
                beforeSend: function () {
                },
                success: function (response) {
                    $('#data').html(response);
                },
                complete: function () {
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log('something went wrong :(');
                }
            });
        }

        function showSearch(Element) {
            var input = Element.parentNode.querySelector('.searchInput');
            var div = Element.parentNode.querySelector('.nameDiv');
            var button = Element.parentNode.querySelector('.searchButton');
            if (input.style.visibility == 'hidden') {
                button.style.visibility = 'hidden';
                input.style.visibility = 'visible';
                div.style.visibility = 'hidden';
                
                input.focus();
            } else {
                button.style.visibility = 'visible';
                input.style.visibility = 'hidden';
                div.style.visibility = 'visible';
            }
        }

        function showHistory() {
            setRefreshing(false);
            var historyButton = document.querySelector('.historyButton');
            historyButton.innerHTML = 'Обновить';
            historyButton.setAttribute('onclick', 'hideHistory()');
            document.querySelector('.dtpicker').style.visibility = 'visible';
        }

        $('.dtpicker').on('change', function() {
            if (document.querySelector('.dtpicker').value == '') {
                setRefreshing(true);
            } else {
                setRefreshing(false);
                var string = document.querySelector('.dtpicker').value; 
                var formData = "date=" + string.replace('T', ' ');
                $.ajax({
                    type: 'POST', 
                    url: 'changeData.php',
                    data: formData, 

                    beforeSend: function () {
                    },
                    success: function (response) {
                        $('#data').empty();
                        $('#dataTable').empty();
                        resp = response.split('~');
                        $('#dataTable').append(resp[0]);
                        $('#procsTable').append(resp[1]);
                    },
                    complete: function () {
                        addTooltip();
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log('something went wrong :(');
                    }
                });
            }
        });

        function hideHistory() {
            setRefreshing(true);
            let historyButton = document.querySelector('.historyButton');
            historyButton.innerHTML = 'История';
            historyButton.setAttribute('onclick', 'showHistory()');
            document.querySelector('.dtpicker').style.visibility = 'hidden';
        }

        function searchData(isClicked, n) {
            let table = document.getElementById("procsTable");
            let rows = table.rows;
            let value = rows[0].getElementsByTagName("th")[n].firstChild.value;
            if (isClicked) {
                document.getElementById('data').setAttribute('search', value); 
                document.getElementById('data').setAttribute('searchCol', n); 
            } else {
                x.value = value;
                value = document.getElementById('data').getAttribute('search');
            }

            table = document.getElementById('procsTable');
            rows = table.rows;
            for (i = 1; i < (rows.length - 1); i++) {
                let count = 0;
                x = rows[i].getElementsByTagName('td')[n];
                cell = x.innerHTML.toLowerCase();
                value = value.toLowerCase();
                if (!(cell.startsWith(value))) {                         
                    let par = x.closest('tr');
                    let parent = par.parentElement;
                    parent.removeChild(par);
                    if (i != (rows.length - 1)) {
                        i = 0;
                    }
                }
            }
        } 

        function sortTable(n, type) {
            let switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            let table = document.getElementById("procsTable");
            let div = document.getElementById('data');
            let dir = div.getAttribute('dir');

            if (type == 0) {
                if (div.getAttribute('sort') == n) {
                    if (dir == 'desc') {
                        $(div).attr('dir', 'asc');
                    } else {
                        $(div).attr('dir', 'desc');
                    }
                }
                $(div).attr('sort', n);
            }

            switching = true;
            let isInt = false;
            let isBytes = false;
            let rows = table.rows;
            let check = rows[1].getElementsByTagName("td")[n];

            if (check.hasAttribute('value') ) {
                isBytes = true;
            } else if (!isNaN(check.innerHTML)) {
                isInt = true;
            }

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 2); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("td")[n];
                    y = rows[i + 1].getElementsByTagName("td")[n];
                    
                    if (isBytes) {
                        x1 = parseInt(x.getAttribute('value'));
                        y1 = parseInt(y.getAttribute('value'));
                    } else if (isInt) {
                        x1 = parseInt(x.innerHTML);
                        y1 = parseInt(y.innerHTML);
                    } else {
                        x1 = x.innerHTML.toLowerCase();
                        y1 = y.innerHTML.toLowerCase();
                    }

                    if (dir == "asc") {
                        if (x1 > y1) {
                            shouldSwitch= true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x1 < y1) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    ++switchcount;      
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }

function changeData() {
    $.ajax({
        type: 'POST', 
        url: 'changeData.php',  
        success: function (response) {
            $('#data').empty();
            $('#dataTable').empty();
            let resp = response.split('~');
            $('#dataTable').append(resp[0]);
            $('#procsTable').append(resp[1]);
        },
        complete: function () {
            let div = document.getElementById('data');
            let sort = div.getAttribute('sort');
            let search = div.getAttribute('searchCol');
            if (search > -1) {
                searchData(false, search);
            }
            if (sort > -1) {
                sortTable(sort, 1);
            }
                    
            checkMemory();
            let tableOffset = $("table").eq(1).offset().top;
            addTooltip();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            console.log('something went wrong with ajax :(');
        }
    });
}

        function checkMemory() {
            let table = document.getElementById("dataTable");
            let rows = table.rows;
            let check = rows[2].getElementsByTagName("td")[0];
            if (check.innerHTML > 80) {
                showAlert(check.innerHTML);
                check.style.background = 'rgb(240, 198, 187)';
            } else if (check.innerHTML > 60) {
                check.style.background = 'rgb(240, 230, 187)';
            }
        }

        function showAlert(note) {
            document.getElementById('thead').style.top = '18px';
            let box = $('#ready');
            box.find('.message').text('Критический уровень загруженности памяти: ' + note + '%.');
            box.find('.alertButton').unbind().click(function() {
                box.hide();
            });

            box.find(".alertButton").click(function() {
                document.getElementById('thead').style.top = '0px';
            });

            box.show();
        }

        </script>
    </body>
</html>