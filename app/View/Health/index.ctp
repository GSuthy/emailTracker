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


                <?php

                $myfile = file_get_contents("http://starscream.byu.edu/test.txt");
                echo $myfile;
                //uncomment the lines below if you need to print out the CanItClient searchlog
                // $print = CanItClient::searchlog();
                // print_r($print);
            
             ?>

            
            </tbody>
        
        </table>
    </div>
</div>



