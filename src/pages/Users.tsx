import React, { useState } from 'react';
import { Card, Table, Button, Modal, Form, Alert } from 'react-bootstrap';

interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin';
  status: 'active' | 'inactive';
  lastActive: string;
}

const Users: React.FC = () => {
  const [users, setUsers] = useState<User[]>([
    {
      id: 1,
      name: 'Admin User',
      email: 'admin@example.com',
      role: 'admin',
      status: 'active',
      lastActive: '2024-03-20 10:30:00'
    }
  ]);
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleAddUser = (e: React.FormEvent) => {
    e.preventDefault();
    // Add user logic here
    setShowAddModal(false);
    setSuccess('User added successfully');
  };

  const handleEditUser = (e: React.FormEvent) => {
    e.preventDefault();
    // Edit user logic here
    setShowEditModal(false);
    setSuccess('User updated successfully');
  };

  const handleDeleteUser = (userId: number) => {
    // Delete user logic here
    setSuccess('User deleted successfully');
  };

  return (
    <div className="container-fluid">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <Button variant="danger" onClick={() => setShowAddModal(true)}>
          <i className="bi bi-plus-lg me-2"></i>
          Add New User
        </Button>
      </div>

      {error && <Alert variant="danger">{error}</Alert>}
      {success && <Alert variant="success">{success}</Alert>}

      <Card className="border-0 shadow-sm">
        <Card.Body>
          <Table responsive hover>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Active</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.map((user) => (
                <tr key={user.id}>
                  <td>{user.name}</td>
                  <td>{user.email}</td>
                  <td>
                    <span className="badge bg-danger">{user.role}</span>
                  </td>
                  <td>
                    <span className={`badge bg-${user.status === 'active' ? 'success' : 'secondary'}`}>
                      {user.status}
                    </span>
                  </td>
                  <td>{user.lastActive}</td>
                  <td>
                    <Button
                      variant="outline-primary"
                      size="sm"
                      className="me-2"
                      onClick={() => {
                        setSelectedUser(user);
                        setShowEditModal(true);
                      }}
                    >
                      <i className="bi bi-pencil"></i>
                    </Button>
                    <Button
                      variant="outline-danger"
                      size="sm"
                      onClick={() => handleDeleteUser(user.id)}
                    >
                      <i className="bi bi-trash"></i>
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </Table>
        </Card.Body>
      </Card>

      {/* Add User Modal */}
      <Modal show={showAddModal} onHide={() => setShowAddModal(false)}>
        <Modal.Header closeButton>
          <Modal.Title>Add New User</Modal.Title>
        </Modal.Header>
        <Form onSubmit={handleAddUser}>
          <Modal.Body>
            <Form.Group className="mb-3">
              <Form.Label>Name</Form.Label>
              <Form.Control type="text" required />
            </Form.Group>
            <Form.Group className="mb-3">
              <Form.Label>Email</Form.Label>
              <Form.Control type="email" required />
            </Form.Group>
            <Form.Group className="mb-3">
              <Form.Label>Password</Form.Label>
              <Form.Control type="password" required />
            </Form.Group>
            <Form.Group className="mb-3">
              <Form.Label>Confirm Password</Form.Label>
              <Form.Control type="password" required />
            </Form.Group>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={() => setShowAddModal(false)}>
              Cancel
            </Button>
            <Button variant="danger" type="submit">
              Add User
            </Button>
          </Modal.Footer>
        </Form>
      </Modal>

      {/* Edit User Modal */}
      <Modal show={showEditModal} onHide={() => setShowEditModal(false)}>
        <Modal.Header closeButton>
          <Modal.Title>Edit User</Modal.Title>
        </Modal.Header>
        <Form onSubmit={handleEditUser}>
          <Modal.Body>
            <Form.Group className="mb-3">
              <Form.Label>Name</Form.Label>
              <Form.Control
                type="text"
                defaultValue={selectedUser?.name}
                required
              />
            </Form.Group>
            <Form.Group className="mb-3">
              <Form.Label>Email</Form.Label>
              <Form.Control
                type="email"
                defaultValue={selectedUser?.email}
                required
              />
            </Form.Group>
            <Form.Group className="mb-3">
              <Form.Label>Status</Form.Label>
              <Form.Select defaultValue={selectedUser?.status}>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </Form.Select>
            </Form.Group>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={() => setShowEditModal(false)}>
              Cancel
            </Button>
            <Button variant="danger" type="submit">
              Save Changes
            </Button>
          </Modal.Footer>
        </Form>
      </Modal>
    </div>
  );
};

export default Users; 