<div class="container">
    <div class="starter-template">
        <div class="row">
            <div class="col-8">
                <h1>Bulk following in Twitter</h1>
                <form action="/" method="POST">
                    <div class="form-group">
                        <label for="product">Insert a link to the Product Hunt project or to the Twitter profile</label>
                        <input placeholder="E.g., https://www.producthunt.com/posts/surreal-app"
                               class="form-control" type="text" name="productName" value="<?php
                        if (isset($_POST["action"]) && $_POST["action"] === "View") {
                            echo $_POST["productName"];
                        } ?>">
                    </div>
                    <input type="submit" class="btn btn-primary" name="action" value="View">
                </form>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-8">
                <?php if (isset($messageText)) { ?>
                    <div class="alert alert-info">
                        <?php echo $messageText; ?>
                    </div>
                <?php } ?>
                <?php if (isset($list)) { ?>
                    <p class="lead">There are <?php echo count($list); ?> users from Twitter
                        who have subscribed for your link:</p>
                    <form action="/" method="POST">
                        <input type="submit" class="btn btn-success" name="action" value="Subscribe for all"></p>
                    </form>
                    <?php
                    foreach ($list as $element) {
                        if ($element !== "") {
                            echo "<li><a target= \"_blank\"href=\"https://twitter.com/" . $element . "\">$element</a></li>";
                        }
                    }
                }
                ?></div>
        </div>
    </div>
</div><!-- /.container -->
