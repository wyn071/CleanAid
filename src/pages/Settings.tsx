import React, { useState } from 'react';
import { Card, Form, Button, Alert, Tabs, Tab } from 'react-bootstrap';

const Settings: React.FC = () => {
  const [activeTab, setActiveTab] = useState('profile');
  const [profileForm, setProfileForm] = useState({
    email: '',
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });
  const [preferences, setPreferences] = useState({
    notifications: true,
    theme: 'light',
    language: 'en'
  });
  const [security, setSecurity] = useState({
    twoFactorEnabled: false,
    lastPasswordChange: '2024-03-15'
  });
  const [message, setMessage] = useState<{ type: 'success' | 'danger', text: string } | null>(null);

  const handleProfileSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // TODO: Implement profile update logic
    setMessage({ type: 'success', text: 'Profile updated successfully!' });
  };

  const handlePreferencesChange = (key: string, value: any) => {
    setPreferences(prev => ({ ...prev, [key]: value }));
    // TODO: Implement preferences update logic
    setMessage({ type: 'success', text: 'Preferences updated successfully!' });
  };

  const handleSecurityChange = (key: string, value: any) => {
    setSecurity(prev => ({ ...prev, [key]: value }));
    // TODO: Implement security settings update logic
    setMessage({ type: 'success', text: 'Security settings updated successfully!' });
  };

  return (
    <div className="container-fluid">
      <div className="mb-4">
        <h2>Settings</h2>
        <p className="text-muted">Manage your account settings and preferences</p>
      </div>

      {message && (
        <Alert variant={message.type} onClose={() => setMessage(null)} dismissible>
          {message.text}
        </Alert>
      )}

      <Card className="border-0 shadow-sm">
        <Card.Body>
          <Tabs
            activeKey={activeTab}
            onSelect={(k) => setActiveTab(k || 'profile')}
            className="mb-4"
          >
            <Tab eventKey="profile" title="Profile">
              <Form onSubmit={handleProfileSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Email Address</Form.Label>
                  <Form.Control
                    type="email"
                    value={profileForm.email}
                    onChange={(e) => setProfileForm(prev => ({ ...prev, email: e.target.value }))}
                    placeholder="Enter your email"
                  />
                </Form.Group>

                <h5 className="mb-3">Change Password</h5>
                <Form.Group className="mb-3">
                  <Form.Label>Current Password</Form.Label>
                  <Form.Control
                    type="password"
                    value={profileForm.currentPassword}
                    onChange={(e) => setProfileForm(prev => ({ ...prev, currentPassword: e.target.value }))}
                    placeholder="Enter current password"
                  />
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Label>New Password</Form.Label>
                  <Form.Control
                    type="password"
                    value={profileForm.newPassword}
                    onChange={(e) => setProfileForm(prev => ({ ...prev, newPassword: e.target.value }))}
                    placeholder="Enter new password"
                  />
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Label>Confirm New Password</Form.Label>
                  <Form.Control
                    type="password"
                    value={profileForm.confirmPassword}
                    onChange={(e) => setProfileForm(prev => ({ ...prev, confirmPassword: e.target.value }))}
                    placeholder="Confirm new password"
                  />
                </Form.Group>

                <Button variant="danger" type="submit">
                  Update Profile
                </Button>
              </Form>
            </Tab>

            <Tab eventKey="preferences" title="Preferences">
              <Form>
                <Form.Group className="mb-3">
                  <Form.Label>Theme</Form.Label>
                  <Form.Select
                    value={preferences.theme}
                    onChange={(e) => handlePreferencesChange('theme', e.target.value)}
                  >
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                    <option value="system">System</option>
                  </Form.Select>
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Label>Language</Form.Label>
                  <Form.Select
                    value={preferences.language}
                    onChange={(e) => handlePreferencesChange('language', e.target.value)}
                  >
                    <option value="en">English</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                  </Form.Select>
                </Form.Group>

                <Form.Group className="mb-3">
                  <Form.Check
                    type="switch"
                    id="notifications"
                    label="Enable Notifications"
                    checked={preferences.notifications}
                    onChange={(e) => handlePreferencesChange('notifications', e.target.checked)}
                  />
                </Form.Group>

                <Button variant="danger" onClick={() => handlePreferencesChange('save', true)}>
                  Save Preferences
                </Button>
              </Form>
            </Tab>

            <Tab eventKey="security" title="Security">
              <Form>
                <Form.Group className="mb-3">
                  <Form.Check
                    type="switch"
                    id="twoFactor"
                    label="Enable Two-Factor Authentication"
                    checked={security.twoFactorEnabled}
                    onChange={(e) => handleSecurityChange('twoFactorEnabled', e.target.checked)}
                  />
                  <Form.Text className="text-muted">
                    Add an extra layer of security to your account
                  </Form.Text>
                </Form.Group>

                <div className="mb-3">
                  <p className="mb-1">Last Password Change</p>
                  <small className="text-muted">{security.lastPasswordChange}</small>
                </div>

                <Button variant="danger" onClick={() => handleSecurityChange('save', true)}>
                  Update Security Settings
                </Button>
              </Form>
            </Tab>
          </Tabs>
        </Card.Body>
      </Card>
    </div>
  );
};

export default Settings; 