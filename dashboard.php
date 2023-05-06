<?php

include_once('connection.php');
include('session.php');
// Number of results per page
$perPage = 10;

// Current page number, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate offset
$offset = ($page - 1) * $perPage;

// Initialize search query and search parameters
$search_query = '';
$search_param = '';

// Modify SQL query to include search condition if search query is set
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
    $search_param = '&search=' . $search_query;
    $sql = "SELECT COUNT(*) as total FROM tenders WHERE number LIKE '%$search_query%' OR org LIKE '%$search_query%' OR name LIKE '%$search_query%'";
    //$sql = "SELECT * FROM tenders WHERE number LIKE '%$search_query%' OR org LIKE '%$search_query%' OR name LIKE '%$search_query%' ORDER BY startdate DESC LIMIT $perPage OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $totalRows = $row['total'];
    $totalPages = ceil($totalRows / $perPage);

    $sql = "SELECT * FROM tenders WHERE number LIKE '%$search_query%' OR org LIKE '%$search_query%' OR name LIKE '%$search_query%' ORDER BY startdate DESC LIMIT $perPage OFFSET $offset";
} else {
    // Get total number of rows in the table
    $sql = "SELECT COUNT(*) as total FROM tenders";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $totalRows = $row['total'];

    // Calculate total number of pages
    $totalPages = ceil($totalRows / $perPage);

    // Get rows for the current page
    $sql = "SELECT * FROM tenders ORDER BY startdate DESC LIMIT $perPage OFFSET $offset";
}

// Execute SQL query
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Tender v 1.0</title>
	<link rel="stylesheet" type="text/css" href="bootstrap2/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="datatable/dataTable.bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="my.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
</head>
<body>

<?php include_once('menus.php'); ?>	

<div class="container-fluid">
	<div class="row">
		 <div class="col-lx-12">
			<div class="row">
<?php include_once('/mod/not.php'); ?>	
			</div>
<form class="navbar-form navbar-left"  action="" method="post">
    <div class="form-group">
		 <input type="text" class="form-control" name="search_query" placeholder="Поиск..." value="<?php echo isset($_POST['search_query']) ? $_POST['search_query'] : (isset($_GET['search']) ? $_GET['search'] : ''); ?>">
	</div>
    <button type="submit" class="btn btn-default" name="search">Найти</button>
</form>
			<div class="row">	
				<table id="myTable" class="table table-hover table-bordered table-striped"  style="width: 100%;">
					<thead>
						<th>ID</th>
						<th>Номер</th>
						<th>Организация</th>
						<th>Название тендера</th>
						<th>Сумма</th>
						<th>Дата начало</th>
						<th>Дата конец</th>
						<th>Осталось времени</th>
						<th>Информация</th>
					</thead>
					<tbody>
						<?php
							
						// Установка количества элементов на странице и текущей страницы
						$perPage = 10;
						$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

						// Вычисление OFFSET для текущей страницы
						$offset = ($currentPage - 1) * $perPage;

						// Проверка наличия параметров поиска в сессии
						if(isset($_SESSION['search_query'])) {
							$search_query = $_SESSION['search_query'];

							// Изменение SQL-запроса, чтобы он включал условие поиска
							$sql = "SELECT * FROM tenders WHERE number LIKE '%$search_query%' OR org LIKE '%$search_query%' OR name LIKE '%$search_query%' ORDER BY startdate DESC LIMIT $perPage OFFSET $offset";
						} else {
							// SQL-запрос без условия поиска для отображения всех элементов
							$sql = "SELECT * FROM tenders ORDER BY startdate DESC LIMIT $perPage OFFSET $offset";
						}

						// Проверка отправки формы поиска и сохранение параметров поиска в сессии
						if(isset($_GET['search'])) {
							$search_query = $_GET['search_query'];
							$_SESSION['search_query'] = $search_query;

							// Изменение SQL-запроса, чтобы он включал условие поиска
							$sql = "SELECT * FROM tenders WHERE number LIKE '%$search_query%' OR org LIKE '%$search_query%' OR name LIKE '%$search_query%' ORDER BY startdate DESC LIMIT $perPage OFFSET $offset";
						}

						// Исполнение SQL-запроса
						$query = $conn->query($sql);

							while($row = $query->fetch_assoc()){						
							  $number = $row['number'];
							  $current_date = date('Y-m-d H:i:s');
								?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['number']; ?></td>
        <td><a href="card.php?orgcode=<?php echo $row['orgcode']; ?>" target="_blank"><?php echo $row['org']; ?></a>	
		</td>
        <td><a href="<?php echo $row['link']; ?>" target="_blank"><?php echo $row['name']; ?></a>
		</td>
        <td><?php echo $row['summa']; ?></td>
        <td><?php echo $row['startdate']; ?></td>
        <td><?php echo $row['enddate']; ?></td>
        <td>
            <div id="timer-<?php echo $number; ?>"></div>
        </td>
		 <td><?php echo 'Буду участвовать' ?></td>
    </tr>
	    <?php		
		include('mod/sc.php');
								include('edit_delete_modal.php');
								
}
						?>
					</tbody>
				</table>
			</div>
		</div>
<div class="row">
    <div class="col-md-12 text-center">
        <nav>
            <ul class="pagination">
                <?php 
                // добавляем параметры поиска в ссылки на предыдущую и следующую страницу
                $search_param = '';
                if (isset($_POST['search'])) {
                    $search_query = $_POST['search_query'];
                    $search_param = '&search=' . $search_query;
                } else if (isset($_GET['search'])) {
                    $search_query = $_GET['search'];
                    $search_param = '&search=' . $search_query;
                }
				
				if ($page > 1) : ?>
                <li>
                    <a href="?page=<?php echo $page - 1 . $search_param; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($page > 3) : ?>
                <li>
                    <a href="?page=1<?php echo $search_param; ?>">1</a>
                </li>
                <?php if ($page > 4) : ?>
                    <li>
                        <span>...</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++) : ?>
                <?php if ($i == $page) : ?>
                    <li class="active">
                        <a href="?page=<?php echo $i . $search_param; ?>"><?php echo $i; ?></a>
                    </li>
                <?php else : ?>
                    <li>
                        <a href="?page=<?php echo $i . $search_param; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages - 2) : ?>
                <?php if ($page < $totalPages - 3) : ?>
                    <li>
                        <span>...</span>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="?page=<?php echo $totalPages . $search_param; ?>"><?php echo $totalPages; ?></a>
                </li>
            <?php endif; ?>

            <?php if ($page < $totalPages) : ?>
                <li>
                    <a href="?page=<?php echo $page + 1 . $search_param; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
</div>
<?php
// Close connection
mysqli_close($conn);
	?>	
	</div>
</div>
<?php include('sc2.php') ?>
</body>
<footer><div class="PP"><p>Be happy:<a> SMART LIVE</a></p></div></footer>
</html>
