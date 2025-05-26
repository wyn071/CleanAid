
import React from 'react';
import { Nav } from 'react-bootstrap';
import { Link, useLocation } from 'react-router-dom';

const Sidebar: React.FC = () => {
  const location = useLocation();

  const isActive = (path: string) => location.pathname === path;

  return (
    <div className="sidebar bg-white border-end" style={{ width: '250px', minHeight: '100vh' }}>
      <div className="p-3 border-bottom">
        <div className="d-flex align-items-center">
          <img src="/dswd-logo.png" alt="DSWD Logo" style={{ width: '40px', height: '40px', marginRight: '10px' }} />
          <h5 className="text-danger mb-0">CleanAid</h5>
        </div>
      </div>
      <Nav className="flex-column p-3">
        <Nav.Link as={Link} to="/dashboard" className={`mb-2 ${isActive('/dashboard') ? 'text-danger' : 'text-dark'}`}>
          <i className="bi bi-speedometer2 me-2"></i> Dashboard
        </Nav.Link>
        <Nav.Link as={Link} to="/upload" className={`mb-2 ${isActive('/upload') ? 'text-danger' : 'text-dark'}`}>
          <i className="bi bi-cloud-upload me-2"></i> Data Upload
        </Nav.Link>
        <Nav.Link as={Link} to="/review-data" className={`mb-2 ${isActive('/review-data') ? 'text-danger' : 'text-dark'}`}>
          <i className="bi bi-search me-2"></i> Data Review
        </Nav.Link>
        <Nav.Link as={Link} to="/exports" className={`mb-2 ${isActive('/exports') ? 'text-danger' : 'text-dark'}`}>
          <i className="bi bi-download me-2"></i> Export Data
        </Nav.Link>
        <Nav.Link as={Link} to="/settings" className={`mb-2 ${isActive('/settings') ? 'text-danger' : 'text-dark'}`}>
          <i className="bi bi-gear me-2"></i> Settings
        </Nav.Link>
      </Nav>
    </div>
  );
};

export default Sidebar;
