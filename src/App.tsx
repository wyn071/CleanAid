import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import PrivateRoute from './components/PrivateRoute';
import Layout from './components/Layout';

import Landing from './pages/Landing';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Upload from './pages/Upload';
import Help from './pages/Help';
import Settings from './pages/Settings';
import NotFound from './pages/NotFound';
import ExportPage from './pages/ExportPage';
import ReviewPage from './pages/ReviewPage';
import DuplicateReview from './pages/DuplicateReview';

import './App.css';

const App: React.FC = () => {
  return (
    <Router>
      <AuthProvider>
        <Routes>
          {/* Public routes */}
          <Route path="/" element={<Landing />} />
          <Route path="/login" element={<Login />} />
          <Route path="/signup" element={<Login />} />

          {/* Protected routes */}
          <Route
            path="/dashboard"
            element={
              <PrivateRoute>
                <Layout><Dashboard /></Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/upload"
            element={
              <PrivateRoute>
                <Layout><Upload /></Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/exports"
            element={
              <PrivateRoute>
                <Layout><ExportPage /></Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/review-data"
            element={
              <PrivateRoute>
                <Layout><ReviewPage /></Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/review-duplicates"
            element={
              <PrivateRoute>
                <Layout><DuplicateReview /></Layout>
              </PrivateRoute>
            }
          />
          <Route
            path="/settings"
            element={
              <PrivateRoute>
                <Layout><Settings /></Layout>
              </PrivateRoute>
            }
          />

          {/* Catch-all */}
          <Route path="*" element={<NotFound />} />
        </Routes>
      </AuthProvider>
    </Router>
  );
};

export default App;
