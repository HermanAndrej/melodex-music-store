<?php
require_once 'UserDao.php';

$userDao = new UserDao();

$newUser = [
    'Name' => 'testuser',
    'Email' => 'test@example.com',
    'DateOfBirth' => '1990-01-01',
    'Address' => '123 Test St',
    'Phone' => '255-555-5555',
    'Password' => password_hash('mypassword', PASSWORD_DEFAULT) // Hash the password
];

$insertSuccess = $userDao->insert($newUser);
echo $insertSuccess ? "User inserted successfully.\n" : "User insertion failed.\n";

$users = $userDao->getAll();
echo "Users in DB:\n";
print_r($users);

$user = $userDao->getByEmail('test@example.com');
echo "User retrieved by email:\n";
print_r($user);

if ($user) {
    $updateSuccess = $userDao->update($user['UserID'], ['Name' => 'updateduser']);
    echo $updateSuccess ? "User updated successfully.\n" : "User update failed.\n";

    $deleteSuccess = $userDao->delete($user['UserID']);
    echo $deleteSuccess ? "User deleted successfully.\n" : "User deletion failed.\n";
} else {
    echo "User not found, skipping update and delete tests.\n";
}
?>
