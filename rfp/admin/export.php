<?php
require_once('../../Connections/adminConn.php');

if (isset($_GET['efunc'])) { 
	switch ($_GET['efunc']) {
		case 'proposals':
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=ProjectProposals.csv');
			
			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');
			
			// output the column headings
			fputcsv($output, array('Project', 'First Name', 'Last Name', 'Title', 'Email', 'Company', 'Address', 'City', 'State', 'Zip', 'Trade', 'Phone', 'Mobile', 'Fax', 'Proposal Type', 'Status'));
			
			// fetch the data
			$query = "SELECT projName, FirstName, LastName, Title, Email, Company, Address, City, State, Zip, Trade, Phone, Mobile, Fax, proposalType, statusName FROM projects, demographics, bids, vt_proposaltype, vt_status WHERE (bids.projectID=projects.id AND bids.subID=demographics.id AND bids.appType=vt_proposaltype.id) AND (bids.status=vt_status.idStatus) AND bids.projectID=".$_GET['id'];
			$result = mysql_query($query, $adminConn) or die(mysql_error());
			
			// loop over the rows, outputting them
			while ($row = mysql_fetch_assoc($result)) fputcsv($output, $row);
			exit;
			break;
		case 'contractors':
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=Contractors.csv');
			
			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');
			
			// output the column headings
			fputcsv($output, array('First Name', 'Last Name', 'Title', 'Email', 'Company', 'Address', 'City', 'State', 'Zip', 'Trade', 'Phone', 'Mobile', 'Fax'));
			
			// fetch the data
			$query = "SELECT FirstName, LastName, Title, Email, Company, Address, City, State, Zip, Trade, Phone, Mobile, Fax FROM demographics AS d WHERE d.delete=0";
			$result = mysql_query($query, $adminConn) or die(mysql_error());
			
			// loop over the rows, outputting them
			while ($row = mysql_fetch_assoc($result)) fputcsv($output, $row);
			exit;
			break;
		case 'planlist':
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=ProjectPlanList.csv');
			
			// create a file pointer connected to the output stream
			$output = fopen('php://output', 'w');
			
			// output the column headings
			fputcsv($output, array('Sheet', 'Title', 'Plan Type', 'Revision Date'));
			
			// fetch the data
			$query = "SELECT * FROM (SELECT sheet, title, vt_plantype.planType AS planType, revisionDate FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and projectID=".$_GET['id']." ORDER BY sheet ASC) AS tbl GROUP BY sheet";
			$result = mysql_query($query, $adminConn) or die(mysql_error());
			
			// loop over the rows, outputting them
			while ($row = mysql_fetch_assoc($result)) fputcsv($output, $row);
			exit;
			break;
	}
} else {
	echo "Error creating export file";
	exit;
}
?>