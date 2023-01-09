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
            table, th, td {
                border: 1px solid black;
            }
        </style>
    </head>

    <body> 
    <h1>Dinner!</h1>
    <Button class="btn">Give me a dinner!</Button>
    <div><span>Filter by tag: </span><input name="filter"></input></div>
        
        <h2 id="display"></h2>
 
        <?php 
            if(isset($_GET['response']) ){
                echo $_GET['response'];
            }
            // echo $response;
            // echo "<p>status : ".$status. "</p>"; 
        ?>
        
        <p><a href="/dinner/insert.php">Insert a new dinner</a></p>


        <?php

            if(!empty($data)){
                
                

                echo "
                <table id ='dinner-table'>
                    <tr>
                        <th>Dinner</th>
                        <th>Author</th>
                        <th>Tags</th>
                        <th></th>
                    </tr>
                ";
                foreach($data as &$d){
                    echo "<tr>";
        


                    $delete_button = sprintf('<a href="/dinner/delete.php?id=%s">Delete</a>', $d->id);
                    $update_button = sprintf('<a href="/dinner/update.php?id=%s">Update</a>', $d->id);
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

                echo "<table>";
            }else{
                echo "NO RESULT";
            }




  
        

        ?>
        




        <script>

            const GREY_BLUE = "#a5bbe6";
            const DEEP_BLUE = "#0a59f7";
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
                    oCells.item(j).style.color=GREY_BLUE;
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
                
                if(filter_value == undefined){

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

            const btn = document.querySelector('.btn');
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
                    if(target == ""){
                        for( j = 0; j != 3; j++){
                            oCells.item(j).style.color=GREY_BLUE;
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

        <?php
                    $user = $login_session->get_user();
            
                    if(!empty($user)){
                        echo "USER: ".$user, "  ";
                        echo "<a href=\"/auth/logout.php\">Logout</a><br/>";
                        
                    }
                    else{
                        echo "<a href=\"/auth/login.php\">Login</a><br/>";
                    }
                    
        ?>
</html>