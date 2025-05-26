import React from 'react';
import { Navbar, Nav, Container, Offcanvas, Dropdown } from 'react-bootstrap';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const Layout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const location = useLocation();
  const navigate = useNavigate();
  const { user, logout } = useAuth();

  const isActive = (path: string) => {
    return location.pathname === path;
  };

  const handleLogout = async () => {
    try {
      await logout();
    } catch (error) {
      console.error('Logout failed:', error);
    }
  };

  return (
    <div className="d-flex">
      {/* Sidebar */}
      <div className="sidebar">
        <div className="p-3">
          <div className="d-flex align-items-center mb-4">
            <img src="/dswd-logo.png" alt="DSWD Logo" style={{ height: '40px' }} className="me-2" />
            <h4 className="text-danger mb-0">CleanAid</h4>
          </div>
          <Nav className="flex-column">
            <Nav.Link
              as={Link}
              to="/"
              className={isActive('/') ? 'active' : ''}
            >
              <i className="bi bi-speedometer2"></i>
              Dashboard
            </Nav.Link>
            <Nav.Link
              as={Link}
              to="/upload"
              className={isActive('/upload') ? 'active' : ''}
            >
              <i className="bi bi-cloud-upload"></i>
              Upload Data
            </Nav.Link>
            <Nav.Link
              as={Link}
              to="/review-data"
              className={isActive('/review-data') ? 'active' : ''}
            >
              <i className="bi bi-flag"></i>
              Review Data
            </Nav.Link>
            <Nav.Link
              as={Link}
              to="/exports"
              className={isActive('/exports') ? 'active' : ''}
            >
              <i className="bi bi-download"></i>
              Export Data
            </Nav.Link>
            <Nav.Link
              as={Link}
              to="/settings"
              className={isActive('/settings') ? 'active' : ''}
            >
              <i className="bi bi-gear"></i>
              Settings
            </Nav.Link>
          </Nav>
        </div>
      </div>

      {/* Main Content */}
      <div className="main-content">
        {/* Top Navbar */}
        <Navbar bg="white" className="border-bottom">
          <Container fluid>
            <Navbar.Brand href="#home" className="d-md-none">
              <img src="/dswd-logo.png" alt="DSWD Logo" style={{ height: '30px' }} className="me-2" />
              CleanAid
            </Navbar.Brand>
            <Navbar.Toggle aria-controls="offcanvasNavbar-expand-md" />
            <Navbar.Offcanvas
              id="offcanvasNavbar-expand-md"
              aria-labelledby="offcanvasNavbarLabel-expand-md"
              placement="end"
            >
              <Offcanvas.Header closeButton>
                <Offcanvas.Title id="offcanvasNavbarLabel-expand-md">
                  CleanAid
                </Offcanvas.Title>
              </Offcanvas.Header>
              <Offcanvas.Body>
                <Nav className="ms-auto">
                  <Dropdown align="end">
                    <Dropdown.Toggle variant="link" id="user-dropdown" className="text-dark text-decoration-none">
                      <i className="bi bi-person-circle me-2"></i>
                      {user?.name || 'Admin'}
                    </Dropdown.Toggle>
                    <Dropdown.Menu>
                      <Dropdown.Item as={Link} to="/settings">
                        <i className="bi bi-gear me-2"></i>
                        Settings
                      </Dropdown.Item>
                      <Dropdown.Divider />
                      <Dropdown.Item onClick={handleLogout}>
                        <i className="bi bi-box-arrow-right me-2"></i>
                        Logout
                      </Dropdown.Item>
                    </Dropdown.Menu>
                  </Dropdown>
                </Nav>
              </Offcanvas.Body>
            </Navbar.Offcanvas>
          </Container>
        </Navbar>

        {/* Page Content */}
        <div className="page-content">
          {children}
        </div>
      </div>
    </div>
  );
};

export default Layout; 