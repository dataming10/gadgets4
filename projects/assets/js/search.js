document.getElementById("searchInput").addEventListener("input", function() {
    var input = this.value.toLowerCase();
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "search.php?q=" + encodeURIComponent(input), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.error) {
                    console.error(response.error);
                } else {
                    var items = response.items;
                    updateTable(items);
                }
            } else {
                console.error("Error: " + xhr.status);
            }
        }
    };    
    xhr.send();
});

function updateTable(items) {
    var tableBody = document.getElementById("itemsTableBody");
    tableBody.innerHTML = "";
    items.forEach(function(item) {
        var row = document.createElement("tr");
        row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.product_num}</td>
            <td>${item.name}</td>
            <td style="color: ${item.quantity == 0 ? 'red' : 'inherit'}">${item.quantity}</td>
            <td><img src="${item.image}" alt="Image" style="width: 150px; height: 150px;"></td>
            <td>
                <a href="edit_item.php?id=${item.id}">Edit</a> |
                <a href="delete_item.php?id=${item.id}" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a> |
                <a href="${item.status == 1 ? 'deactivate' : 'activate'}_item.php?id=${item.id}" onclick="return confirm('Are you sure you want to ${item.status == 1 ? 'deactivate' : 'activate'} this item?')">${item.status == 1 ? 'Deactivate' : 'Activate'}</a>
            </td>
        `;
        tableBody.appendChild(row);
    });
}