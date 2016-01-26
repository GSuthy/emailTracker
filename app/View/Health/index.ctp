<?php
$this->start('script');
echo $this->Html->script(array(
    'queues'
));
$this->end();
$this->start('css');
echo $this->Html->css(array(
    'queues'
));
$this->end();
?>

<?php if (!$queues_authorized): ?>

    <div class='container-error''>
	<form class='error'>
        <div class='rowError'>
            <h1>Email Tracking &amp; Filtering</h1>
            <h2>You are not authorized to view this page.</h2>
            <h2>If you believe you have received this message in error, please contact the Office of Information Technology's help desk at 801-422-4000</h2>
        </div>
    </form>
	</div>

<?php else: ?>


<div class="container">
    <div class="tableHolder">
    <table id="queueTable">
    <thead>
            <tr>
                <?php $table = HealthController::error();
                $count = (count($table));
                // echo $count; 
                if (count($table) > 1): ?>
                    <th><FONT COLOR = "B90000"> 10% Disk Space or Less</FONT></th> <?php
                else: ?>
                    <th>All servers have more than 10% Disk Space</th> <?php endif; ?>
            </tr>
            </thead>
            <br>
        <table id="queueTable">
            <thead>
            <tr>
                <th>Hostname</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>

                <?php
                    $dataTable = HealthController::error(); 
                     $i = 0;
                foreach ($dataTable as $result): ?>
                    <tr class="<?= $i++ % 2 == 0 ? 'even' : 'odd' ?>">
                        <td><?= $result['hostname']?></td>
                        <td><FONT COLOR = "B90000"> Error </FONT></td>
                    </tr>
                    <br>
                    <br>
                    <br>
                <?php endforeach;?>
            
            </tbody>
        
        </table>
    </div>
</div>


<?php endif; ?>

<div class="container">
    <div class="tableHolder">
    <table id="queueTable">
    <thead>
            <tr>
                <?php $header = HealthController::copytocluster();
               $test = array();
               foreach ($header as $checker){
                       array_push($test, $checker['message']);
                }

               if ($test == ['No errors']):?>
                <th>Copy To Cluster</th> <?php
                else: ?> 
                <th><FONT COLOR = "B90000"> THERE IS AN ISSUE WITH THE COPY CLUSTER</FONT></th> <?php endif; ?>

            </tr>
            </thead>
            <br>
        <table id="queueTable">
            <thead>
            <tr>
                <th>Status</th>
                <th>Time Checked</th>
            </tr>
            </thead>
            <tbody>
               <?php
                    $dataTable2 = HealthController::copytocluster(); 
                     $i = 0;
                     
                     foreach ($dataTable2 as $dateModification) {
                        if ($dateModification['when_checked'] > 1){
                        $dateModified = $dateModification['when_checked'];
                    }
                }
              
                $time = date("F j g:i a", $dateModified);
                    
                foreach ($dataTable2 as $result2): ?>
                    <tr class="<?= $i++ % 2 == 0 ? 'even' : 'odd' ?>">
                        <td><?= $result2['message']?></td>
                        <td><?= $time ?></td>
                    </tr>

                <?php endforeach;?>


            </tbody>
        
        </table>

        <br>
        <table = id="queueTable">

        <?php

            #The following line is important because it is the configuration file for each server
            #That way when we go from dev to stg, there is no need to configure any differences



            $config_results = shell_exec('head -n 1 /opt/phproot/emailtracking_config_file');

            $clear_cache = shell_exec("wget --no-cache {$config_results}");
            $clear_cache;
            $output = shell_exec("wget --output-document - --quiet -N {$config_results}");


            ?>
            <thead>
                <th>Sent</th>
                <th>Received</th>
                <th>IP</th>
            </thead>
            <tbody>
                <tr>
                    <td><FONT COLOR = #088A29>
                    <?php
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            #$less = $count - 3;
                            #echo $output_one[$less];

                            $less = $count - 2;

                            echo $output_one[$less];
                         ?>
                    </FONT>
                    </td>
                    <td><FONT COLOR = #088A29>
                        <?php 
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            $lesser = $count - 2;
                            echo $output_one[$lesser];
                        ?>
                    </FONT>
                    </td>
                    <td><FONT COLOR = #088A29>
                        <?php 
                             $output_one = explode("!", $output);
                             $count = count($output_one);
                             $least = $count - 4;
                             echo $output_one[$least];
                        ?>
                    </FONT>
                    </td>
                </tr>
                <tr>
                    <td><FONT COLOR = #088A29>
                            <?php
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            $less = $count - 6;
                            echo $output_one[$less];


                            ?>
                        </FONT>
                    </td>
                    <td><FONT COLOR = #088A29>
                            <?php
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            $lesser = $count - 5;
                            echo $output_one[$lesser];
                            ?>
                        </FONT>
                    </td>
                    <td><FONT COLOR = #088A29>
                            <?php
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            $least = $count - 7;
                            echo $output_one[$least];
                            ?>
                        </FONT>
                    </td>
                </tr>
                <tr>
                    <td><FONT COLOR = #088A29>
                            <?php
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            $less = $count - 9;
                            echo $output_one[$less];


                            ?>
                        </FONT>
                    </td>
                    <td><FONT COLOR = #088A29>
                            <?php
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            $lesser = $count - 8;
                            echo $output_one[$lesser];
                            ?>
                        </FONT>
                    </td>
                    <td><FONT COLOR = #088A29>
                            <?php
                            $output_one = explode("!", $output);
                            $count = count($output_one);
                            $least = $count - 10;
                            echo $output_one[$least];
                            ?>
                        </FONT>
                    </td>
                </tr>
            </tbody>
        </table>

        <br>
        <table = id="queueTable">

        <?php

                #config_errors simply returns the server that this part of the program needs to go to
                #in order to get the data. However, the reason it is not hardcoded in is so that when we
                #go from dev->stg->prod we don't manually have to make changes to the different servers

                $config_errors = shell_exec('cat /opt/phproot/emailtracking_config_file | grep "." | tail -1');
                $clear_cache_errors = shell_exec("wget --no-cache {$config_errors}");
                $clear_cache_errors;
                $error = shell_exec("wget --output-document - --quiet -N {$config_errors}");

                ?>
            <thead>
                <th></th>
                <th>Email Error Sent Time</th>
              <th></th>
            </thead>
            <tbody>
            <tr>
                <td><FONT COLOR = #FE2E2E>
                        <?php

                            $output_error = explode("!", $error);
                            $count = count($output_error);
                            $less = $count - 2;
                            echo $output_error[$less];
                        ?>
                    </FONT>
                </td>
                <td><FONT COLOR = #FE2E2E>
                        <?php
                            $output_error = explode("!", $error);
                            $count = count($output_error);
                            $lesser = $count - 3;
                            echo $output_error[$lesser];
                        ?>
                    </FONT>
                </td>
                <td><FONT COLOR = #FE2E2E>
                        <?php
                            $output_error = explode("!", $error);
                            $count = count($output_error);
                            $least = $count - 4;
                            echo $output_error[$least];
                        ?>
                    </FONT>
                </td>
            </tr>
            </tbody>
            <h4><FONT COLOR = #FE2E2E><center>Before Alerting Anyone Please Run A Check Yourself</center></FONT></h4>

        </table>
            
    </div>
</div>



