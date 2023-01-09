<?php

    include 'dinner.php';
    include '../auth/session.php';
    $login_session = new Login_Session();


    $dinner = new Dinner();
    $response = $dinner->get_dinners();

    $decoded = json_decode($response);
    $status = $decoded->status;

    
    $data = $decoded->response;


?>
<!DOCTYPE html>
<html>
    <head>
        <title>Dinner</title>

        <style>
            /* table, th, td {
                border: 1px solid black;
            } */
        </style>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    </head>

    <body> 
    

    <div class="container">
    <nav class="nav py-3">
        <a class="nav-link active" href="/dinner/index.php">Home Page</a>
        <a class="nav-link" href="/report.html">Report</a>
        <a class="nav-link" href="/figure.html">Diagrams</a>
        
 
        <?php
                    $user = $login_session->get_user();
            
                    if(!empty($user)){
                        echo "<span class = 'p-2'>USER: ".$user, "  </span>";
                        echo "<a href=\"/auth/logout.php\" type='button' class='btn btn-dark'>Logout</a>";
                        
                    }
                    else{
                        echo "<a type='button' class='btn btn-info' href=\"/auth/login.php\">Login</a>";
                    }
                    
            ?>
    </nav>

    <h1>Dinner!</h1>

    <div class="row">

            
        <div class="col">

            <Button type="button" class="btn btn-primary" id="give">Give me a dinner!</Button>
        </div>
        <div class="col">
            <div class="row">
                <div class="col">
                <div class="py-2"><span>Filter by tag: </span></div>
                </div>
                <div class="col-9">
                <input name="filter" class="form-control"></input>
                </div>
            </div>
        </div>

        
    </div>

    <div class="row">
        <div class="col">

            </div>
    </div>
    <div class="row">
        <h2 id="display"></h2>
        
        <h3 style="color:crimson;">
        <?php 
            if(isset($_GET['response']) ){
                echo $_GET['response'];
            }
            // echo $response;
            // echo "<p>status : ".$status. "</p>"; 
        ?>
        </h3>
    </div>

    <?php

    if(!empty($user)){
        echo '<p><a type="button" class="btn btn-secondary p-2" href="/dinner/insert.php">Insert a new dinner</a></p>';
    }
    else{
    }
    
    ?>
        


            <div class="row d-flex justify-content-center">
                <div class="col">
        <?php

            if(!empty($data)){
                
            

                echo "
                <table id ='dinner-table' class='table table-hover'>
                <thead>
                    <tr>
                        <th>Dinner</th>
                        <th>Author</th>
                        <th>Tags</th>
                        <th></th>
                    </tr>
                </thead>
                </tbody>
                ";
                foreach($data as &$d){
                    echo "<tr>";
                    
          
                    
                    $delete_button = "";
                    $update_button = "";
                    if($user == $d->username){

                        $delete_button = sprintf('<a href="/dinner/delete.php?id=%s" type="button " class="btn btn-danger">Delete</a>', $d->id);
                        $update_button = sprintf('<a href="/dinner/update.php?id=%s" type="button" class="btn btn-info">Update</a>', $d->id);
                    }
                    echo sprintf(
                        "
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s %s</td>
                        ", 
                        $d->dinner, 
                        $d->username, 
                        implode(", ", $d->tags),
                        $delete_button, $update_button);
                    echo "</tr>";
                }

                echo "</tbody></table>";
            }else{
                echo "NO RESULT";
            }
        ?>
        </div></div>
        

    </div>


        <script>

            const GREY_BLUE = "#c6a6f7";
            const DEEP_BLUE = "#403aba";
            var oTable = document.getElementById('dinner-table');
            // console.log(oTable);
            
            dinner_tag = {};
            all_dinner_array = []
            var row_number = oTable.rows.length;

            console.log(row_number);
            for(i = 0 ; i != row_number; i++){
                
                if(i == 0) continue;
                var oCells = oTable.rows.item(i).cells;

                var dinner_name = oCells.item(0).innerHTML;
                all_dinner_array.push(dinner_name);


                var dinner_tags = oCells.item(2).innerHTML.split(',');
                dinner_tags = dinner_tags.map(x => x.replace(" ", ""));

                for( j = 0; j != 3; j++){
                    oCells.item(j).style.color=DEEP_BLUE;
                }
                        

                for(j = 0; j != dinner_tags.length; j++ ){
                    
                    
                    if(dinner_tags[j] == "") continue;
                    if(dinner_tag[dinner_tags[j]] != undefined){

                        dinner_tag[dinner_tags[j]].push(dinner_name);
                    }else{
                        dinner_tag[dinner_tags[j]] = [dinner_name];
                    }
                }

            }

            console.log(dinner_tag);
            console.log(all_dinner_array);
            
            var filter_value = undefined;
            function clickHandler(event) {
                
                if(filter_value == undefined || filter_value == ""){

                    const idx = Math.floor(Math.random() * all_dinner_array.length);
                    document.getElementById('display').innerHTML = "Lucky Restaruant : " + all_dinner_array[idx] + "!";
                }else{

                    var final_arr = []
                    for (const [key, value] of Object.entries(dinner_tag)) {
                        console.log(filter_value, key,  value);

                        if(key.includes(filter_value)){

                            final_arr = final_arr.concat(value);
                        }

                        
                    }console.log(final_arr)

                    const idx = Math.floor(Math.random() * final_arr.length);
                    document.getElementById('display').innerHTML = "Lucky Restaruant : " + final_arr[idx] + "!";
                }

            }

            const btn = document.getElementById('give');
            btn.addEventListener('click', clickHandler);
            
            const input = document.querySelector('input');
            input.addEventListener('input', updateValue);
            function updateValue(e) {

                var target = e.target.value;
                // console.log(target);
                filter_value = target;

                

                for(i = 0 ; i != row_number; i++){
                
                    if(i == 0) continue;
                    var oCells = oTable.rows.item(i).cells;

                    var dinner_name = oCells.item(0).innerHTML;
                    var tag_string = oCells.item(2).innerHTML;
                    if(target == "" || filter_value == undefined){
                        for( j = 0; j != 3; j++){
                            oCells.item(j).style.color=DEEP_BLUE;
                        }
                        
                    }else{
                        if(tag_string.includes(target)){
                            for( j = 0; j != 3; j++){
                                oCells.item(j).style.color=DEEP_BLUE;
                            }
                        }else{
                            for( j = 0; j != 3; j++){
                                oCells.item(j).style.color=GREY_BLUE;
                            }
                        }
                    }
    

            }
            }
        </script>

</html>