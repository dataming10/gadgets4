<!-- items_table.php -->

<table>
    <tr>
        <th>ID</th>
        <th>Serial</th>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Image</th>
        <th>Actions</th>
        <?php if($is_admin) { echo '<th>Action</th>'; } ?>
    </tr>
    <?php while($row = $result_items->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['product_num']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td style="color: <?php echo ($row['quantity'] == 0) ? 'red' : 'inherit'; ?>"><?php echo $row['quantity']; ?></td>
            <td><img src="<?php echo $row['image']; ?>" alt="Image" style="width: 150px; height: 150px;"></td>
            <?php if($is_admin == 0) { ?>
                <td>
                    <a href="edit_item.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="delete_item.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a> |
                    <?php
                $status_text = ($row['status'] == 1) ? 'Deactivate' : 'Activate';
                $status_link = 'activate_deactivate_item.php?id=' . $row['id'];
            ?>
            <a href="<?php echo $status_link; ?>" onclick="return confirm('Are you sure you want to deactivate this item?')"><?php echo $status_text; ?></a>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
</table>
