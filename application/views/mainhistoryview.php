<div class="container">
    <div class="starter-template">
        <div class="row">
            <div class="col-8">
                <h1>Bulk following in Twitter</h1>
                <p>History of following</p>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Link</th>
                        <th>Following</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $array) { ?>
                        <tr>
                            <td><?php echo $array["date"] ?></td>
                            <td><?php echo $array["link"] ?></td>
                            <td><?php echo implode(", ", json_decode($array["subscription"])); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- /.container -->
