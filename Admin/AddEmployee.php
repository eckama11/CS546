<?php
    require_once(dirname(__FILE__)."/../common.php");
    if (!isset($loginSession))
        doUnauthenticatedRedirect();
    if (!$loginSession->isAdministrator)
        doUnauthorizedRedirect();
?>
		<div class="container padded">
    		<div class="row" >
    			<div class="col-md-3 col-md-offset-5" style="padding-bottom:10px; outline: 10px solid black;">
           			<form role="form">
           			<ul class="nav nav-tabs">
           				<li>
  						<div class="form-group">
    						<label for="name">Name</label>
    						<input type="text" class="form-control" name="name" id="name" placeholder="Enter name">
  						</div>
  						<div class="form-group">
    						<label for="address" style="color:black;">Address</label>
    						<input type="text" class="form-control" name="address" id="address" placeholder="Enter Address">
  						</div>
  					</li>
  					<li>
  						<div class="form-group">
  							<label for="rank">Rank</label>
   							<select class="form-control" name="rank" id="rank">
   								<option selected disabled id="">Select One</option>
                                <?php
                                    $ranks = $db->readRanks();
                                    foreach ($ranks as $rank) {
                                        echo '<option id="'. htmlentities($rank->id) .'">'. htmlentities($rank->name) .'</option>';
                                    }
                                ?>
							</select>
  						</div>
  						<div class="form-group">
  							<label for="taxid">Tax ID</label>
    						<input type="text" class="form-control" name="taxid" id="taxid" placeholder="Enter Soc Sec #">
  						</div>
  						<div class="form-group">
  							<label for="numDeductions">Number of Deductions</label>
    						<input type="text" class="form-control" name="numDeductions" id="numDeductions" placeholder="Enter a number">
  						</div>
  						<div class="form-group">
  							<label for="salary">Salary</label>
    						<input type="text" class="form-control" name="salary" id="salary" placeholder="Enter a salary">
  						</div>
  						
  						<div class="form-group">
  							<label for="departments">Departments</label>
  							<select multiple class="form-control" name="departments" id="departments">
  								<option selected disabled id="">Select At Least One</option>
                                <?php
                                    $depts = $db->readDepartments();
                                    foreach ($depts as $dept) {
                                        echo '<option id="'. htmlentities($dept->id) .'">'. htmlentities($dept->name) .'</option>';
                                    }
                                ?>
  							</select>
  						</div>
  						<div class="checkbox">
    						<label>
      							<input type="checkbox"> Check me out
   							</label>
  						</div>
  						<button type="submit" class="btn btn-default">Submit</button>
					</li>
					</ul>
					</form>
				</div>
			</div>
		</div>